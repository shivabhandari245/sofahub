// production-materials.js
class ProductionMaterials {
    constructor() {
this.batchproductId = window.batchproductId;
        this.currentMaterials = [];
        this.isUpdateMode = false;
        
        this.initElements();
        this.bindEvents();
        this.initPage();
    }

    initElements() {
        // DOM Elements
        this.$categorySelect = $('#material_category');
        this.$searchInput = $('#searchMaterial');
        this.$materialsTableBody = $('#availableMaterialsTable tbody');
        this.$quantityModal = $('#quantityModal');
        this.$allocateQuantity = $('#allocateQuantity');
        this.$confirmAllocationBtn = $('#confirmAllocation');
        this.$allocatedTable = $('#allocatedTable');
        this.$confirmBtn = $('#confirmBtn');
        this.$selectedCount = $('#selectedCount');
        this.$totalCost = $('#totalCost');
    }

    bindEvents() {
        // Category and search
        this.$categorySelect.on('change', () => this.handleCategoryChange());
        this.$searchInput.on('input', () => this.handleSearch());
        
        // Modal buttons
        this.$confirmAllocationBtn.on('click', () => this.handleAllocationConfirm());
        
        // Dynamic event delegation
        $(document).on('click', '.allocateBtn', (e) => this.handleAllocateClick(e));
        $(document).on('click', '.deleteBtn', (e) => this.handleDeleteClick(e));
        
        // Modal close reset
        this.$quantityModal.on('hidden.bs.modal', () => this.resetModal());
    }

    initPage() {
        this.$searchInput.prop('disabled', true);
        this.fetchAllocatedMaterials();
    }

    // ========== AVAILABLE MATERIALS SECTION ==========

    handleCategoryChange() {
        const categoryId = this.$categorySelect.val();
        if (!categoryId) {
            this.resetMaterialsTable();
            this.$searchInput.prop('disabled', true);
            return;
        }
        
        this.fetchMaterialsByCategory(categoryId);
    }

    fetchMaterialsByCategory(categoryId) {
        this.showLoading('#availableMaterialsTable tbody', 'Loading materials...');
        
        $.ajax({
            url: `/admin/raw-materials/by-category/${categoryId}`,
            type: 'GET',
            success: (response) => {
                if (response.success && response.materials?.length > 0) {
                    this.currentMaterials = response.materials;
                    this.populateMaterialsTable(this.currentMaterials);
                    this.$searchInput.prop('disabled', false);
                } else {
                    this.showEmptyTable('No materials found for this category');
                }
            },
            error: (xhr) => {
                console.error('Error loading materials:', xhr);
                this.showError('Failed to load materials. Please try again.');
                this.showEmptyTable('Error loading materials');
            }
        });
    }

    handleSearch() {
        const term = this.$searchInput.val().toLowerCase();
        const filtered = this.currentMaterials.filter(m => {
            return m.name.toLowerCase().includes(term) ||
                   (m.supplier?.name?.toLowerCase().includes(term)) ||
                   (m.storage_location?.toLowerCase().includes(term));
        });
        this.populateMaterialsTable(filtered);
    }

    populateMaterialsTable(materials) {
        this.$materialsTableBody.empty();
        
        if (!materials || materials.length === 0) {
            this.showEmptyTable('No materials found');
            return;
        }

        materials.forEach(material => {
            const supplier = material.supplier?.name || 'N/A';
            const unit = material.unit?.name || '';
            const category = material.category?.name || 'Unknown';
            const quantity = material.quantity || 0;
            const unitCost = parseFloat(material.unit_cost || 0).toFixed(2);
            const isDisabled = quantity <= 0;
            
            const row = `
                <tr>
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
                            data-category="${this.escapeHtml(category)}"
                            ${isDisabled ? 'disabled' : ''}>
                            <i class="fas fa-plus"></i> Allocate
                        </button>
                    </td>
                </tr>
            `;
            this.$materialsTableBody.append(row);
        });
    }

    // ========== ALLOCATION HANDLING ==========

    handleAllocateClick(e) {
        const $btn = $(e.currentTarget);
        const materialId = $btn.data('id');
        const materialName = $btn.data('name');
        const availableStock = parseFloat($btn.data('stock'));
        const unit = $btn.data('unit');
        const unitCost = $btn.data('cost');
        const category = $btn.data('category');
        
        this.checkExistingAllocation(materialId, materialName, availableStock, unit, unitCost, category);
    }

    checkExistingAllocation(materialId, materialName, availableStock, unit, unitCost, category) {
        $.ajax({
            url: `/admin/check-allocation/${this.batchproductId}/${materialId}`,
            type: 'GET',
            success: (response) => {
                if (response.allocated && response.data) {
                    this.showDuplicateAlert(response.data, materialId, materialName, availableStock, unit, unitCost, category);
                } else {
                    this.showAllocationModal(materialId, materialName, availableStock, unit, unitCost, category);
                }
            },
            error: () => {
                this.showError('Failed to check allocation status');
            }
        });
    }

    showDuplicateAlert(existingData, materialId, materialName, availableStock, unit, unitCost, category) {
        Swal.fire({
            title: 'Material Already Allocated',
            html: `
                <div class="text-start">
                    <p><strong>${materialName}</strong> is already allocated to this batch.</p>
                    <div class="alert alert-info p-2">
                        <p class="mb-1"><strong>Current Allocation:</strong> ${existingData.quantity_used} ${unit}</p>
                        <p class="mb-1"><strong>Available Stock:</strong> ${availableStock} ${unit}</p>
                        <p class="mb-0"><strong>Remaining Stock:</strong> ${availableStock - existingData.quantity_used} ${unit}</p>
                    </div>
                    <p>Do you want to update the allocation?</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                this.showUpdateModal(existingData, materialId, materialName, availableStock, unit, unitCost, category);
            }
        });
    }

    showAllocationModal(materialId, materialName, availableStock, unit, unitCost, category) {
        this.setupModal(materialId, materialName, availableStock, unit, unitCost, category, false);
        this.$quantityModal.modal('show');
    }

    showUpdateModal(existingData, materialId, materialName, availableStock, unit, unitCost, category) {
        this.setupModal(materialId, materialName, availableStock, unit, unitCost, category, true, existingData.quantity_used);
        this.$quantityModal.modal('show');
    }

    setupModal(materialId, materialName, availableStock, unit, unitCost, category, isUpdate = false, existingQuantity = 0) {
        const remainingStock = availableStock - existingQuantity;
        const maxQuantity = isUpdate ? remainingStock : availableStock;
        
        // Update modal title and button
        const modalTitle = isUpdate ? 'Update Material Allocation' : 'Allocate Material';
        const buttonText = isUpdate ? 'Update Allocation' : 'Confirm Allocation';
        const buttonIcon = isUpdate ? 'fa-sync' : 'fa-check';
        
        $('.modal-title').html(`<i class="fas ${isUpdate ? 'fa-edit' : 'fa-cube'}"></i> ${modalTitle}`);
        this.$confirmAllocationBtn.html(`<i class="fas ${buttonIcon}"></i> ${buttonText}`);
        
        // Fill modal data
        $('#modalMaterialName').text(materialName);
        $('#modalMaterialDetails').html(`
            <strong>Category:</strong> ${category} | 
            <strong>Unit Cost:</strong> Rs ${unitCost}
            ${isUpdate ? ` | <strong>Current:</strong> ${existingQuantity} ${unit}` : ''}
        `);
        
        $('#availableStockDisplay').text(
            isUpdate 
                ? `${availableStock} ${unit} (Remaining: ${remainingStock} ${unit})`
                : `${availableStock} ${unit}`
        );
        
        this.$allocateQuantity.val('');
        this.$allocateQuantity.attr('max', maxQuantity);
        this.$allocateQuantity.attr('step', unit === 'kg' || unit === 'liters' ? '0.01' : '1');
        this.$allocateQuantity.attr('placeholder', `Enter quantity (max: ${maxQuantity} ${unit})`);
        
        // Set hidden values
        $('#selectedMaterialId').val(materialId);
        $('#selectedMaterialCost').val(unitCost);
        $('#selectedMaterialCategory').val(category);
        
        // Store mode
        this.isUpdateMode = isUpdate;
    }

    handleAllocationConfirm() {
        const materialId = $('#selectedMaterialId').val();
        const quantity = this.$allocateQuantity.val();
        const maxQuantity = this.$allocateQuantity.attr('max');

        // Validation
        if (!quantity || quantity <= 0) {
            this.showError('Please enter a valid quantity');
            return;
        }

        if (parseFloat(quantity) > parseFloat(maxQuantity)) {
            this.showError(`Cannot allocate more than available stock (${maxQuantity})`);
            return;
        }

        this.allocateMaterial(materialId, quantity, this.isUpdateMode);
    }

    allocateMaterial(materialId, quantity, isUpdate = false) {
        $.ajax({
            url: '/admin/allocate-material',
            type: 'POST',
            data: {
                batchproduct_id: this.batchproductId,
                material_id: materialId,
                quantity_used: quantity,
                _token: window.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    this.showSuccess(
                        isUpdate ? 'Material allocation updated successfully!' : 'Material allocated successfully!'
                    );
                    this.$quantityModal.modal('hide');
                    
                    // Update allocated materials
                    this.updateAllocatedMaterials();
                    
                    // Refresh available materials if category is selected
                    const categoryId = this.$categorySelect.val();
                    if (categoryId) {
                        this.fetchMaterialsByCategory(categoryId);
                    }
                } else {
                    this.showError(response.message || 'Failed to allocate material');
                }
            },
            error: (xhr) => {
                const errorMsg = xhr.responseJSON?.message || 'Failed to allocate material';
                this.showError(errorMsg);
            }
        });
    }

    // ========== ALLOCATED MATERIALS SECTION ==========

    fetchAllocatedMaterials() {
        $.ajax({
            url: `/admin/show-used-materials/${this.batchproductId}`,
            type: 'GET',
            success: (response) => {
                if (response.success) {
                    this.updateAllocatedTable(response.data.materials);
                    this.updateCostSummary(response.data.materials);
                }
            },
            error: () => {
                console.error('Failed to fetch allocated materials');
            }
        });
    }

    updateAllocatedMaterials() {
        // Show loading state
        const originalContent = this.$allocatedTable.html();
        
        this.$allocatedTable.html(`
            <tr>
                <td colspan="6" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <span class="ms-2">Updating...</span>
                </td>
            </tr>
        `);
        
        // Fetch updated data after a short delay
        setTimeout(() => {
            this.fetchAllocatedMaterials();
        }, 300);
    }

    updateAllocatedTable(materials) {
        this.$allocatedTable.empty();
        
        if (!materials || materials.length === 0) {
            this.$allocatedTable.html(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No materials allocated yet</p>
                    </td>
                </tr>
            `);
            this.$selectedCount.text('0 items');
            this.$confirmBtn.prop('disabled', true);
            return;
        }

        let html = '';
        materials.forEach(material => {
            html += `
                <tr id="material-${material.id}" class="fade-in">
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
                </tr>
            `;
        });
        
        this.$allocatedTable.html(html);
        this.$selectedCount.text(materials.length + ' items');
        this.$confirmBtn.prop('disabled', false);
    }

    handleDeleteClick(e) {
        const allocationId = $(e.currentTarget).data('id');
        const materialName = $(e.currentTarget).data('material');
        
        Swal.fire({
            title: 'Remove Material?',
            text: `Are you sure you want to remove ${materialName} from allocated materials?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                this.deleteAllocation(allocationId);
            }
        });
    }

    deleteAllocation(allocationId) {
        $.ajax({
            url: `/admin/delete-allocation/${allocationId}`,
            type: 'DELETE',
            data: { _token: window.csrfToken },
            success: (response) => {
                if (response.success) {
                    this.showSuccess('Material removed from allocation');
                    this.updateAllocatedMaterials();
                    
                    // Refresh available materials if category is selected
                    const categoryId = this.$categorySelect.val();
                    if (categoryId) {
                        this.fetchMaterialsByCategory(categoryId);
                    }
                } else {
                    this.showError(response.message || 'Failed to remove allocation');
                }
            },
            error: () => {
                this.showError('Failed to remove allocation');
            }
        });
    }

    // ========== COST CALCULATION ==========

    updateCostSummary(materials) {
        const materialsCost = (materials || []).reduce((total, material) => {
            const tc = parseFloat(material.total_cost) || 0;
            return total + tc;
        }, 0);

        // Update total cost display
        this.$totalCost.text('Rs ' + this.formatCurrency(materialsCost));
    }

    // ========== HELPER METHODS ==========

    resetMaterialsTable() {
        this.$materialsTableBody.html(`
            <tr>
                <td colspan="7" class="table-empty-state">
                    <i class="fas fa-inbox"></i> Select a category to view materials
                </td>
            </tr>
        `);
    }

    showEmptyTable(message) {
        this.$materialsTableBody.html(`
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
        $('.modal-title').html('<i class="fas fa-cube"></i> Allocate Material');
        this.$confirmAllocationBtn.html('<i class="fas fa-check"></i> Confirm Allocation');
        this.isUpdateMode = false;
        this.$allocateQuantity.val('');
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
            timer: 3000
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
            timer: 4000
        });
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.productionMaterials = new ProductionMaterials();
});