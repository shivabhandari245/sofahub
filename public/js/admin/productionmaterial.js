// productionmaterial.js - Optimized Version
class ProductionMaterials {
    constructor() {
        this.batchproductId = window.batchproductId;
        this.currentMaterials = [];
        this.allocatedMaterials = new Map(); // Use Map for faster lookups
        this.isUpdateMode = false;
        this.materialsCache = new Map(); // Cache materials by category
        
        this.initElements();
        this.bindEvents();
        this.loadInitialData();
    }

    initElements() {
        // Cache DOM elements
        this.elements = {
            categorySelect: $('#material_category'),
            searchInput: $('#searchMaterial'),
            materialsTableBody: $('#availableMaterialsTable tbody'),
            quantityModal: $('#quantityModal'),
            allocateQuantity: $('#allocateQuantity'),
            confirmAllocationBtn: $('#confirmAllocation'),
            allocatedTable: $('#allocatedTable'),
            confirmBtn: $('#confirmBtn'),
            selectedCount: $('#selectedCount'),
            totalCost: $('#totalCost'),
            modalTitle: $('.modal-title'),
            modalMaterialName: $('#modalMaterialName'),
            modalMaterialDetails: $('#modalMaterialDetails'),
            availableStockDisplay: $('#availableStockDisplay')
        };
    }

    bindEvents() {
        const { categorySelect, searchInput, confirmAllocationBtn, quantityModal } = this.elements;
        
        // Use debounced events for better performance
        categorySelect.on('change', () => this.handleCategoryChange());
        searchInput.on('input', this.debounce(() => this.handleSearch(), 300));
        
        confirmAllocationBtn.on('click', () => this.handleAllocationConfirm());
        
        // Event delegation with event namespacing
        $(document)
            .off('.productionMaterials')
            .on('click.productionMaterials', '.allocateBtn', (e) => this.handleAllocateClick(e))
            .on('click.productionMaterials', '.deleteBtn', (e) => this.handleDeleteClick(e));
        
        quantityModal.on('hidden.bs.modal', () => this.resetModal());
    }

    async loadInitialData() {
        try {
            // Load allocated materials and available categories simultaneously
            const [allocatedResponse] = await Promise.all([
                this.fetchAllocatedMaterials()
            ]);
            
            // Disable search initially
            this.elements.searchInput.prop('disabled', true);
        } catch (error) {
            console.error('Initialization error:', error);
            this.showError('Failed to initialize page');
        }
    }

    // ========== AVAILABLE MATERIALS SECTION ==========

    async handleCategoryChange() {
        const categoryId = this.elements.categorySelect.val();
        
        if (!categoryId) {
            this.resetMaterialsTable();
            this.elements.searchInput.prop('disabled', true);
            return;
        }
        
        await this.fetchMaterialsByCategory(categoryId);
    }

    async fetchMaterialsByCategory(categoryId) {
        // Check cache first
        if (this.materialsCache.has(categoryId)) {
            const cached = this.materialsCache.get(categoryId);
            this.currentMaterials = cached;
            this.populateMaterialsTable(cached);
            this.elements.searchInput.prop('disabled', false);
            return;
        }
        
        this.showLoading('#availableMaterialsTable tbody', 'Loading materials...');
        
        try {
            const response = await $.ajax({
                url: `/admin/raw-materials/by-category/${categoryId}`,
                type: 'GET',
                cache: true
            });
            
            if (response.success && response.materials?.length > 0) {
                this.currentMaterials = response.materials;
                // Cache the results
                this.materialsCache.set(categoryId, response.materials);
                this.populateMaterialsTable(this.currentMaterials);
                this.elements.searchInput.prop('disabled', false);
            } else {
                this.showEmptyTable('No materials found for this category');
            }
        } catch (xhr) {
            console.error('Error loading materials:', xhr);
            this.showError('Failed to load materials. Please try again.');
            this.showEmptyTable('Error loading materials');
        }
    }

    handleSearch() {
        const term = this.elements.searchInput.val().toLowerCase();
        if (!term.trim()) {
            this.populateMaterialsTable(this.currentMaterials);
            return;
        }
        
        // Use simple string matching for speed
        const filtered = this.currentMaterials.filter(m => 
            m.name.toLowerCase().includes(term) ||
            (m.supplier?.name?.toLowerCase()?.includes(term)) ||
            (m.storage_location?.toLowerCase()?.includes(term))
        );
        
        this.populateMaterialsTable(filtered);
    }

    populateMaterialsTable(materials) {
        const tbody = this.elements.materialsTableBody;
        tbody.empty();
        
        if (!materials || materials.length === 0) {
            this.showEmptyTable('No materials found');
            return;
        }

        // Use DocumentFragment for better performance
        const fragment = document.createDocumentFragment();
        
        materials.forEach(material => {
            const row = this.createMaterialRow(material);
            fragment.appendChild(row);
        });
        
        tbody[0].appendChild(fragment);
    }

    createMaterialRow(material) {
        const tr = document.createElement('tr');
        const supplier = material.supplier?.name || 'N/A';
        const unit = material.unit?.name || '';
        const quantity = material.quantity || 0;
        const unitCost = parseFloat(material.unit_cost || 0).toFixed(2);
        const isDisabled = quantity <= 0;
        
        tr.innerHTML = `
            <td>${this.escapeHtml(material.name)}</td>
            <td>${this.escapeHtml(supplier)}</td>
            <td><span class="badge ${quantity > 0 ? 'bg-success' : 'bg-danger'}">${quantity} ${this.escapeHtml(unit)}</span></td>
            <td>${this.escapeHtml(unit)}</td>
            <td>Rs ${unitCost}</td>
            <td>${this.escapeHtml(material.storage_location || 'N/A')}</td>
            <td>
                <button class="btn btn-primary btn-sm allocateBtn" 
                    data-id="${material.id}"
                    data-name="${this.escapeHtml(material.name)}"
                    data-stock="${quantity}"
                    data-unit="${this.escapeHtml(unit)}"
                    data-cost="${unitCost}"
                    ${isDisabled ? 'disabled' : ''}>
                    <i class="fas fa-plus"></i> Allocate
                </button>
            </td>
        `;
        
        return tr;
    }

    // ========== ALLOCATION HANDLING ==========

    async handleAllocateClick(e) {
        const $btn = $(e.currentTarget);
        const materialId = $btn.data('id');
        const materialName = $btn.data('name');
        const availableStock = parseFloat($btn.data('stock'));
        const unit = $btn.data('unit');
        const unitCost = $btn.data('cost');
        
        try {
            const response = await $.ajax({
                url: `/admin/check-allocation/${this.batchproductId}/${materialId}`,
                type: 'GET',
                cache: false
            });
            
            if (response.allocated && response.data) {
                this.showUpdateModal(response.data, materialId, materialName, availableStock, unit, unitCost);
            } else {
                this.showAllocationModal(materialId, materialName, availableStock, unit, unitCost);
            }
        } catch (error) {
            this.showError('Failed to check allocation status');
        }
    }

    showAllocationModal(materialId, materialName, availableStock, unit, unitCost) {
        this.setupModal(materialId, materialName, availableStock, unit, unitCost, false);
        this.elements.quantityModal.modal('show');
    }

    showUpdateModal(existingData, materialId, materialName, availableStock, unit, unitCost) {
        this.setupModal(materialId, materialName, availableStock, unit, unitCost, true, existingData.quantity_used);
        this.elements.quantityModal.modal('show');
    }

    setupModal(materialId, materialName, availableStock, unit, unitCost, isUpdate = false, existingQuantity = 0) {
        const remainingStock = availableStock - existingQuantity;
        const maxQuantity = isUpdate ? remainingStock : availableStock;
        
        const { modalTitle, modalMaterialName, modalMaterialDetails, availableStockDisplay, confirmAllocationBtn } = this.elements;
        
        // Update modal content
        modalTitle.html(`<i class="fas ${isUpdate ? 'fa-edit' : 'fa-cube'}"></i> ${isUpdate ? 'Update Material Allocation' : 'Allocate Material'}`);
        modalMaterialName.text(materialName);
        
        const detailsHtml = `
            <strong>Unit Cost:</strong> Rs ${unitCost}
            ${isUpdate ? ` | <strong>Current:</strong> ${existingQuantity} ${unit}` : ''}
        `;
        modalMaterialDetails.html(detailsHtml);
        
        availableStockDisplay.text(
            isUpdate 
                ? `${availableStock} ${unit} (Remaining: ${remainingStock} ${unit})`
                : `${availableStock} ${unit}`
        );
        
        const allocateQuantity = this.elements.allocateQuantity;
        allocateQuantity.val('');
        allocateQuantity.attr('max', maxQuantity);
        allocateQuantity.attr('step', unit === 'kg' || unit === 'liters' ? '0.01' : '1');
        allocateQuantity.attr('placeholder', `Enter quantity (max: ${maxQuantity} ${unit})`);
        
        // Update button
        confirmAllocationBtn.html(`<i class="fas ${isUpdate ? 'fa-sync' : 'fa-check'}"></i> ${isUpdate ? 'Update Allocation' : 'Confirm Allocation'}`);
        
        // Store modal data as attributes
        allocateQuantity.data({
            materialId: materialId,
            materialCost: unitCost,
            isUpdate: isUpdate,
            existingQuantity: existingQuantity
        });
        
        this.isUpdateMode = isUpdate;
    }

    async handleAllocationConfirm() {
        const { allocateQuantity } = this.elements;
        const quantity = allocateQuantity.val();
        const maxQuantity = allocateQuantity.attr('max');
        const data = allocateQuantity.data();
        
        // Validation
        if (!quantity || quantity <= 0) {
            this.showError('Please enter a valid quantity');
            return;
        }

        if (parseFloat(quantity) > parseFloat(maxQuantity)) {
            this.showError(`Cannot allocate more than available stock (${maxQuantity})`);
            return;
        }

        try {
            await this.allocateMaterial(data.materialId, quantity, data.isUpdate);
        } catch (error) {
            this.showError('Failed to allocate material');
        }
    }

    async allocateMaterial(materialId, quantity, isUpdate = false) {
        try {
            const response = await $.ajax({
                url: '/admin/allocate-material',
                type: 'POST',
                data: {
                    batchproduct_id: this.batchproductId,
                    material_id: materialId,
                    quantity_used: quantity,
                    _token: window.csrfToken
                }
            });
            
            if (response.success) {
                this.showSuccess(
                    isUpdate ? 'Material allocation updated successfully!' : 'Material allocated successfully!'
                );
                this.elements.quantityModal.modal('hide');
                
                // Update UI immediately without waiting for server response
                this.updateAllocatedMaterials();
                
                // Invalidate cache for current category
                const categoryId = this.elements.categorySelect.val();
                if (categoryId) {
                    this.materialsCache.delete(categoryId);
                    await this.fetchMaterialsByCategory(categoryId);
                }
            } else {
                this.showError(response.message || 'Failed to allocate material');
            }
        } catch (xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Failed to allocate material';
            this.showError(errorMsg);
        }
    }

    // ========== ALLOCATED MATERIALS SECTION ==========

    async fetchAllocatedMaterials() {
        try {
            const response = await $.ajax({
                url: `/admin/show-used-materials/${this.batchproductId}`,
                type: 'GET',
                cache: false
            });
            
            if (response.success) {
                this.updateAllocatedTable(response.data.materials);
                this.updateCostSummary(response.data.materials);
            }
        } catch (error) {
            console.error('Failed to fetch allocated materials:', error);
        }
    }

    updateAllocatedMaterials() {
        // Update UI optimistically
        const tbody = this.elements.allocatedTable;
        const loadingRow = `<tr><td colspan="6" class="text-center py-3">Updating...</td></tr>`;
        tbody.html(loadingRow);
        
        // Fetch updated data
        setTimeout(() => this.fetchAllocatedMaterials(), 100);
    }

    updateAllocatedTable(materials) {
        const tbody = this.elements.allocatedTable;
        tbody.empty();
        
        if (!materials || materials.length === 0) {
            tbody.html(this.createEmptyAllocatedRow());
            this.elements.selectedCount.text('0 items');
            this.elements.confirmBtn.prop('disabled', true);
            return;
        }

        // Build table using DocumentFragment for performance
        const fragment = document.createDocumentFragment();
        
        materials.forEach(material => {
            const row = this.createAllocatedRow(material);
            fragment.appendChild(row);
        });
        
        tbody[0].appendChild(fragment);
        this.elements.selectedCount.text(materials.length + ' items');
        this.elements.confirmBtn.prop('disabled', false);
    }

    createAllocatedRow(material) {
        const tr = document.createElement('tr');
        tr.id = `material-${material.id}`;
        tr.className = 'fade-in';
        
        tr.innerHTML = `
            <td>${this.escapeHtml(material.material_name || 'Unknown')}</td>
            <td>${this.escapeHtml(material.category_name || 'Unknown')}</td>
            <td>${material.quantity_used}</td>
            <td>Rs ${this.formatCurrency(material.unit_cost)}</td>
            <td>Rs ${this.formatCurrency(material.total_cost)}</td>
            <td>
                <button class="btn btn-danger btn-sm deleteBtn" 
                        data-id="${material.id}"
                        data-material="${this.escapeHtml(material.material_name || 'Unknown')}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        return tr;
    }

    createEmptyAllocatedRow() {
        return `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No materials allocated yet</p>
                </td>
            </tr>
        `;
    }

    async handleDeleteClick(e) {
        const allocationId = $(e.currentTarget).data('id');
        const materialName = $(e.currentTarget).data('material');
        
        const result = await Swal.fire({
            title: 'Remove Material?',
            text: `Are you sure you want to remove ${materialName} from allocated materials?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        });
        
        if (result.isConfirmed) {
            try {
                await this.deleteAllocation(allocationId);
            } catch (error) {
                this.showError('Failed to remove allocation');
            }
        }
    }

    async deleteAllocation(allocationId) {
        try {
            const response = await $.ajax({
                url: `/admin/delete-allocation/${allocationId}`,
                type: 'DELETE',
                data: { _token: window.csrfToken }
            });
            
            if (response.success) {
                this.showSuccess('Material removed from allocation');
                
                // Remove row immediately for better UX
                $(`#material-${allocationId}`).remove();
                
                // Update counts and costs
                this.updateAllocatedMaterials();
                
                // Refresh available materials cache
                const categoryId = this.elements.categorySelect.val();
                if (categoryId) {
                    this.materialsCache.delete(categoryId);
                    await this.fetchMaterialsByCategory(categoryId);
                }
            } else {
                this.showError(response.message || 'Failed to remove allocation');
            }
        } catch (error) {
            throw error;
        }
    }

    // ========== COST CALCULATION ==========

    updateCostSummary(materials) {
        const totalCost = (materials || []).reduce((sum, material) => {
            return sum + (parseFloat(material.total_cost) || 0);
        }, 0);
        
        this.elements.totalCost.text('Rs ' + this.formatCurrency(totalCost));
    }

    // ========== HELPER METHODS ==========

    resetMaterialsTable() {
        this.elements.materialsTableBody.html(`
            <tr>
                <td colspan="7" class="table-empty-state">
                    <i class="fas fa-inbox"></i> Select a category to view materials
                </td>
            </tr>
        `);
    }

    showEmptyTable(message) {
        this.elements.materialsTableBody.html(`
            <tr>
                <td colspan="7" class="table-empty-state">
                    <i class="fas fa-search"></i> ${message}
                </td>
            </tr>
        `);
    }

    showLoading(selector, message = 'Loading...') {
        $(selector).html(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <span class="ms-2">${message}</span>
                </td>
            </tr>
        `);
    }

    resetModal() {
        const { modalTitle, confirmAllocationBtn, allocateQuantity } = this.elements;
        
        modalTitle.html('<i class="fas fa-cube"></i> Allocate Material');
        confirmAllocationBtn.html('<i class="fas fa-check"></i> Confirm Allocation');
        allocateQuantity.val('');
        allocateQuantity.removeData();
        this.isUpdateMode = false;
    }

    // Utility methods
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    formatCurrency(amount) {
        return parseFloat(amount || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000 // Reduced from 3000ms
        });
    }

    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000 // Reduced from 4000ms
        });
    }
}

// Initialize when document is ready
$(document).ready(function() {
    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    window.productionMaterials = new ProductionMaterials();
});