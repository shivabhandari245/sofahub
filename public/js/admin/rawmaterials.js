// Raw Materials Management JavaScript
class RawMaterialsManager {
    constructor() {
        this.csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        // Modal elements
        this.addMaterialModal = $('#addMaterialModal');
        this.restockModal = $('#restockModalBackdrop');
        this.categoryModal = $('#modalBackdrop');
        
        // Form elements
        this.materialForm = $('#materialForm');
        this.restockForm = $('#restockForm');
        this.materialId = $('#materialId');
        this.submitBtn = $('#submitBtn');
        this.submitRestockBtn = $('#submitRestockBtn');
        this.formTitle = $('#formTitle');
        this.isEditMode = false;
        this.currentModalType = null;
        this.currentMaterial = null;
        
        this.init();

        this.currentPage = 1;
        this.perPage = 10;
        this.loadMaterials();
    }

    loadMaterials(page = 1) {
    this.currentPage = page;

    $.ajax({
        url: '/admin/rawmaterials',
        data: {
            page: page,
            per_page: this.perPage,
            search: $('#searchInput').val(),
            category: $('#filterCategory').val()
        },
        success: (res) => {
            this.renderTable(res.data);
            this.renderPagination(res.pagination);
        },
        error: () => {
            Swal.fire('Error', 'Failed to load materials', 'error');
        }
    });
}

    init() {
        this.setupEventListeners();
        this.setupSweetAlert();
    }

    setupEventListeners() {
        // Form toggle functionality - Updated for modal
        $('#toggleFormBtn').on('click', () => {
            this.openMaterialModal();
        });

        // Edit button handler - FIXED: Better error handling for data parsing
        $(document).on('click', '.btn-edit', (e) => {
            const button = $(e.currentTarget);
            const materialData = button.data('material');
            
            console.log('Raw material data:', materialData); // Debug log
            
            try {
                // If it's already a parsed object, use it directly
                let material;
                if (typeof materialData === 'object') {
                    material = materialData;
                } else {
                    // If it's a string, parse it as JSON
                    material = JSON.parse(materialData);
                }
                
                console.log('Parsed material:', material); // Debug log
                this.showEditOptions(material);
            } catch (error) {
                console.error('Error parsing material data:', error);
                console.error('Raw data that failed:', materialData);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load material data. Please try again.'
                });
            }
        });

        // Form submission
        this.materialForm.on('submit', (e) => this.handleFormSubmit(e));
        this.restockForm.on('submit', (e) => this.handleRestockSubmit(e));

        // Delete functionality
        $(document).on('click', '.btn-delete', (e) => this.handleDelete(e));

        // Modal functionality
        $('#addCategoryBtn, #addSupplierBtn, #addUnitBtn').on('click', (e) => {
            const type = $(e.target).closest('button').attr('id').replace('add', '').replace('Btn', '').toLowerCase();
            this.openModal(type);
        });

        $('#saveItemBtn').on('click', () => this.handleAddItem());

        // Close modal events - Updated for new modal structure
        $('.modal-close, .closeMaterialModal').on('click', (e) => {
            const modal = $(e.target).closest('.modal-overlay');
            this.closeModal(modal);
        });

        $('#closeRestockModalBtn, #restockModalBackdrop, #cancelRestockBtn').on('click', () => this.closeRestockModal());
        $('#closeModalBtn, #modalBackdrop').on('click', () => this.categoryModal.hide());

        // Close on overlay click for all modals
        $('.modal-overlay').on('click', (e) => {
            if (e.target === e.currentTarget) {
                this.closeModal($(e.currentTarget));
            }
        });

        // ESC key to close modals
        $(document).on('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });

        // Prevent modal close when clicking inside modal content
        $('.modal-content').on('click', (e) => {
            e.stopPropagation();
        });

        // Table filtering
        this.setupTableFiltering();

        // Real-time validation
        this.setupValidation();
    }

    setupSweetAlert() {
        this.Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    // Show edit options (Restock or Correct)
    showEditOptions(material) {
        this.currentMaterial = material;
        
        Swal.fire({
            title: 'Edit Material',
            text: `Choose an action for "${material.name}"`,
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Restock',
            denyButtonText: 'Correct',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#28a745',
            denyButtonColor: '#007bff',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                // Restock option
                this.openRestockModal(material);
            } else if (result.isDenied) {
                // Correct option
                this.editMaterial(material);
            }
        });
    }

    // Open material modal
    openMaterialModal() {
        this.addMaterialModal.fadeIn();
        setTimeout(() => $('#name').focus(), 300);
    }

    // Close material modal
    closeMaterialModal() {
        this.addMaterialModal.hide();
        this.resetForm();
    }

    // Open restock modal
    openRestockModal(material) {
        // Set form values
        $('#restock_material_id').val(material.id);
        $('#restock_material_name').val(material.name);
        $('#restock_available_quantity').val(`${material.quantity} ${material.unit?.name || ''}`);
        $('#restock_quantity').val('');
        $('#restock_unit_cost').val(material.unit_cost);
        
        // Reset validation
        $('#restockForm .is-invalid').removeClass('is-invalid');
        $('#restockForm .invalid-feedback').text('');
        
        this.restockModal.fadeIn();
        setTimeout(() => $('#restock_quantity').focus(), 300);
    }

    // Close restock modal
    closeRestockModal() {
        this.restockModal.hide();
        this.restockForm.trigger('reset');
        this.currentMaterial = null;
    }

    // Generic modal close function
    closeModal(modal) {
        if (modal) {
            modal.hide();
        }
    }

    // Close all modals
    closeAllModals() {
        $('.modal-overlay').hide();
    }

    editMaterial(material) {
        console.log('Editing material with data:', material); // Debug log
        
        this.isEditMode = true;
        this.formTitle.text('Edit Material');
        this.submitBtn.text('Update Material');
        this.materialId.val(material.id);
        
        // Set form values - FIXED: Properly set all field values with better null handling
        $('#name').val(material.name || '');
        $('#category_id').val(material.category_id || '');
        $('#supplier_id').val(material.supplier_id || '');
        $('#quantity').val(material.quantity || '');
        $('#unit_id').val(material.unit_id || '');
        $('#unit_cost').val(material.unit_cost || '');
        $('#storage_location').val(material.storage_location || '');

        // Clear any validation errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        // Open modal AFTER setting values
        this.openMaterialModal();
        
        // Debug: Log current form values
        setTimeout(() => {
            console.log('Form values after setting:', {
                name: $('#name').val(),
                category_id: $('#category_id').val(),
                supplier_id: $('#supplier_id').val(),
                quantity: $('#quantity').val(),
                unit_id: $('#unit_id').val(),
                unit_cost: $('#unit_cost').val(),
                storage_location: $('#storage_location').val()
            });
        }, 100);
    }

    resetForm() {
        this.isEditMode = false;
        this.formTitle.text('Add New Material');
        this.submitBtn.text('Add Material');
        this.materialForm.trigger('reset');
        this.materialId.val('');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    // ... rest of your methods remain the same (handleRestockSubmit, validateRestockForm, etc.)
    // Handle restock form submission
    async handleRestockSubmit(e) {
        e.preventDefault();
        
        if (!this.validateRestockForm()) {
            this.Toast.fire({
                icon: 'error',
                title: 'Please fill in all required fields correctly'
            });
            return;
        }

        const materialId = $('#restock_material_id').val();
        const formData = {
            restock_quantity: parseInt($('#restock_quantity').val()),
            unit_cost: parseFloat($('#restock_unit_cost').val())
        };

        this.submitRestockBtn.prop('disabled', true).html(
            `<span class="spinner-border spinner-border-sm"></span> Restocking...`
        );

        try {
            const response = await fetch(`/admin/restock-material/${materialId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            this.submitRestockBtn.prop('disabled', false).html('Restock Material');
            
            if (data.success) {
                this.Toast.fire({
                    icon: 'success',
                    title: 'Material restocked successfully!'
                });

                this.closeRestockModal();
                this.loadMaterials(this.currentPage);
            }

            else {
                let errorMessage = data.message || 'Failed to restock material';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).join(', ');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Restock Failed',
                    text: errorMessage
                });
            }
        } catch (error) {
            this.submitRestockBtn.prop('disabled', false).html('Restock Material');
            console.error('Error during restock:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'An error occurred. Please check your connection and try again.'
            });
        }
    }

    // Validate restock form
    validateRestockForm() {
        let isValid = true;
        $('#restockForm .is-invalid').removeClass('is-invalid');
        $('#restockForm .invalid-feedback').text('');

        const restockQty = $('#restock_quantity').val();
        const unitCost = $('#restock_unit_cost').val();

        if (!restockQty || restockQty < 1) {
            this.showRestockFieldError('restock_quantity', 'Please enter a valid restock quantity (minimum 1)');
            isValid = false;
        }

        if (!unitCost || unitCost < 0) {
            this.showRestockFieldError('restock_unit_cost', 'Please enter a valid unit cost');
            isValid = false;
        }

        return isValid;
    }

renderTable(materials) {
    let html = '';
    const currentPage = this.currentPage;
    const perPage = this.perPage;

    if (!materials.length) {
        html = `<tr><td colspan="10" class="text-center text-muted">No materials found</td></tr>`;
    }

    materials.forEach((material, index) => {
        const sn = (currentPage - 1) * perPage + index + 1;

        html += `
        <tr>
          
            <td>${sn}</td>

            <td>${material.name}</td>
            <td>${material.category?.name ?? 'N/A'}</td>
            <td>${material.supplier?.name ?? 'N/A'}</td>
            <td>${material.quantity}</td>
            <td>${material.unit?.name ?? 'N/A'}</td>
            <td>Rs. ${material.unit_cost}</td>
            <td>Rs. ${material.total_cost}</td>
            <td>${material.storage_location ?? 'N/A'}</td>

            <td>
                <div class="action-buttons">

                    <!-- ✏️ Edit -->
                    <button type="button"
                        class="btn-action btn-edit"
                        title="Edit Material"
                        data-id="${material.id}"
                        data-material='${JSON.stringify(material)}'>
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <!-- 👁 View -->
                    <button type="button"
                        class="btn-action btn-view"
                        title="View History"
                        onclick="window.location='/admin/viewmaterialhistory/${material.id}'">
                        <i class="bi bi-eye"></i>
                    </button>

                    <!-- 🗑 Delete -->
                    <form action="/admin/deleterawmaterials/${material.id}"
                          method="POST"
                          class="d-inline delete-form">

                        <input type="hidden" name="_token"
                               value="${$('meta[name=csrf-token]').attr('content')}">
                        <input type="hidden" name="_method" value="DELETE">

                        <button type="button"
                            class="btn-action btn-delete"
                            title="Delete"
                            data-id="${material.id}"
                            data-name="${material.name}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>

                </div>
            </td>
        </tr>`;
    });

    $('#materialBody').html(html);
}

 renderPagination(pagination) {
    let html = '';

    for (let i = 1; i <= pagination.last_page; i++) {
        html += `
        <button class="btn btn-sm ${i === pagination.current_page ? 'btn-primary' : 'btn-outline-primary'}"
            data-page="${i}">
            ${i}
        </button>`;
    }

    $('#pagination').html(html);
}


    showRestockFieldError(fieldId, message) {
        const field = $(`#${fieldId}`);
        field.addClass('is-invalid');
        field.next('.invalid-feedback').text(message);
    }

    setupTableFiltering() {
        $('#searchInput, #filterCategory').on('input change', () => {
            this.loadMaterials(1);
        });
    }


    setupValidation() {
        $('input, select').on('blur', (e) => {
            const field = $(e.target);
            this.validateField(field);
        });
    }

    validateField(field) {
        const value = field.val().trim();
        const fieldId = field.attr('id');
        const fieldName = field.attr('name');
        
        if (field.prop('required') && !value) {
            this.showFieldError(fieldId, `${this.getFieldLabel(fieldName)} is required`);
            return false;
        }
        
        // Additional validation for specific fields
        if (fieldId === 'quantity' && value < 0) {
            this.showFieldError(fieldId, 'Quantity cannot be negative');
            return false;
        }
        
        if (fieldId === 'unit_cost' && value < 0) {
            this.showFieldError(fieldId, 'Unit cost cannot be negative');
            return false;
        }
        
        field.removeClass('is-invalid').next('.invalid-feedback').text('');
        return true;
    }

    getFieldLabel(fieldName) {
        const labels = {
            'name': 'Material Name',
            'category_id': 'Category',
            'supplier_id': 'Supplier',
            'quantity': 'Quantity',
            'unit_id': 'Unit',
            'unit_cost': 'Unit Cost',
            'storage_location': 'Storage Location'
        };
        return labels[fieldName] || fieldName;
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        if (!this.validateForm()) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please fill in all required fields correctly',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        const url = this.isEditMode ? `/admin/updaterawmaterials/${this.materialId.val()}` : '/admin/addrawmaterials';
        const method = this.isEditMode ? 'PUT' : 'POST';

        this.submitBtn.prop('disabled', true).html(
            `<span class="spinner-border spinner-border-sm"></span> ${this.isEditMode ? 'Updating...' : 'Adding...'}`
        );

        try {
            const formData = new FormData();
            formData.append('_token', this.csrfToken);
            formData.append('_method', method);
            formData.append('name', $('#name').val().trim());
            formData.append('category_id', $('#category_id').val());
            formData.append('supplier_id', $('#supplier_id').val() || '');
            formData.append('quantity', $('#quantity').val());
            formData.append('unit_id', $('#unit_id').val());
            formData.append('unit_cost', $('#unit_cost').val());
            formData.append('storage_location', $('#storage_location').val().trim());

            if (this.isEditMode) {
                formData.append('id', this.materialId.val());
            }

            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            this.submitBtn.prop('disabled', false).html(this.isEditMode ? 'Update Material' : 'Add Material');
            
            if (data.success) {
                this.Toast.fire({
                    icon: 'success',
                    title: this.isEditMode ? 'Material updated successfully!' : 'Material added successfully!'
                });

                this.closeMaterialModal();
                this.loadMaterials(this.currentPage);
            }


             else {
                let errorMessage = data.message || 'Unknown error occurred';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).join(', ');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Operation Failed',
                    text: errorMessage
                });
            }
        } catch (error) {
            this.submitBtn.prop('disabled', false).html(this.isEditMode ? 'Update Material' : 'Add Material');
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Network Error',
                text: 'An error occurred. Please check your connection and try again.'
            });
        }
    }

    validateForm() {
        let isValid = true;
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        const fields = [
            { id: 'name', name: 'Material Name' },
            { id: 'category_id', name: 'Category' },
            { id: 'quantity', name: 'Quantity' },
            { id: 'unit_id', name: 'Unit' },
            { id: 'unit_cost', name: 'Unit Cost' }
        ];

        fields.forEach(field => {
            const fieldElement = $(`#${field.id}`);
            if (!this.validateField(fieldElement)) {
                isValid = false;
            }
        });

        return isValid;
    }

    showFieldError(fieldId, message) {
        const field = $(`#${fieldId}`);
        field.addClass('is-invalid');
        field.next('.invalid-feedback').text(message);
    }

    handleDelete(e) {
        const button = $(e.currentTarget);
        const materialId = button.data('id');
        const materialName = button.data('name');
        
        Swal.fire({
            title: 'Delete Material?',
            text: `Are you sure you want to delete "${materialName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = button.closest('form');
                const originalHtml = button.html();
                
                button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                
                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: {
                        _token: this.csrfToken,
                        _method: 'DELETE'
                    },
                    success: (data) => {
                        button.prop('disabled', false).html(originalHtml);
                        if (data.success) {
                            this.Toast.fire({ 
                                icon: 'success', 
                                title: 'Material deleted successfully!' 
                            });
                           this.loadMaterials(this.currentPage);

                        } else {
                            Swal.fire({ 
                                icon: 'error', 
                                title: 'Delete Failed', 
                                text: data.message 
                            });
                        }
                    },
                    error: (xhr) => {
                        button.prop('disabled', false).html(originalHtml);
                        let errorMessage = 'Delete failed. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({ 
                            icon: 'error', 
                            title: 'Error', 
                            text: errorMessage 
                        });
                    }
                });
            }
        });
    }

    openModal(type) {
        this.currentModalType = type;
        const titles = { 
            category: 'Categories', 
            supplier: 'Suppliers', 
            unit: 'Units' 
        };
        
        $('#modalTitle').text(`Manage ${titles[type]}`);
        $('#listTitle').text(titles[type]);
        $('#inputLabel').text(`New ${titles[type].slice(0, -1)} Name`);
        $('#newItemInput').val('');
        this.loadExistingItems(type);
        this.categoryModal.fadeIn();
        setTimeout(() => $('#newItemInput').focus(), 300);
    }

    loadExistingItems(type) {
        $.get(`/admin/list${type}`, (items) => {
            let html = items.length ? items.map(item => `
                <div class="item-row">
                    <span>${item.name}</span>
                    <button type="button" class="btn-action btn-delete btn-sm" onclick="window.rawMaterialsManager.deleteItem('${type}', ${item.id}, '${item.name.replace(/'/g, "\\'")}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `).join('') : '<div class="text-muted">No items found</div>';
            $('#existingList').html(html);
        }).fail(() => {
            $('#existingList').html('<div class="text-muted">Error loading items</div>');
        });
    }

    deleteItem(type, id, name) {
        // Direct deletion without SweetAlert confirmation for categories, units, suppliers
        $.ajax({
            url: `/admin/delete${type}/${id}`,
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            },
            success: (data) => {
                if (data.success) {
                    this.Toast.fire({ 
                        icon: 'success', 
                        title: 'Item deleted successfully!' 
                    });
                    this.loadExistingItems(type);
                    this.refreshSelectOptions(type);
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Delete Failed', 
                        text: data.message 
                    });
                }
            },
            error: (xhr) => {
                let errorMessage = 'Delete failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: errorMessage 
                });
            }
        });
    }

    refreshSelectOptions(type) {
        $.get(`/admin/list${type}`, (items) => {
            const selectId = type + '_id';
            const $select = $(`#${selectId}`);
            $select.empty().append(`<option value="">Select ${type.charAt(0).toUpperCase() + type.slice(1)}</option>`);
            
            items.forEach(item => {
                $select.append(new Option(item.name, item.id));
            });
        }).fail(() => {
            console.error(`Failed to load ${type} options`);
        });
    }

    handleAddItem() {
        const name = $('#newItemInput').val().trim();
        if (!name) {
            Swal.fire({ 
                icon: 'warning', 
                title: 'Input Required', 
                text: 'Please enter a name' 
            });
            return;
        }

        const saveBtn = $('#saveItemBtn');
        const originalHtml = saveBtn.html();
        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: `/admin/add${this.currentModalType}`,
            method: 'POST',
            data: { 
                _token: this.csrfToken,
                name: name 
            },
            success: (data) => {
                saveBtn.prop('disabled', false).html(originalHtml);
                if (data.success) {
                    this.Toast.fire({ 
                        icon: 'success', 
                        title: `${this.currentModalType.charAt(0).toUpperCase() + this.currentModalType.slice(1)} added successfully!` 
                    });
                    $('#newItemInput').val('');
                    this.loadExistingItems(this.currentModalType);
                    this.refreshSelectOptions(this.currentModalType);
                    setTimeout(() => $('#newItemInput').focus(), 100);
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Add Failed', 
                        text: data.message 
                    });
                }
            },
            error: (xhr) => {
                saveBtn.prop('disabled', false).html(originalHtml);
                let errorMessage = 'Error adding item. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    html: errorMessage 
                });
            }
        });
    }
}

// Make the class available globally
window.RawMaterialsManager = RawMaterialsManager;

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof RawMaterialsManager !== 'undefined') {
        window.rawMaterialsManager = new RawMaterialsManager();
    }
});

$(document).on('click', '#pagination button', function () {
    const page = $(this).data('page');
    window.rawMaterialsManager.loadMaterials(page);
});
