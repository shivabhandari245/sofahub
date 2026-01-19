$(document).ready(function() {
    ProductionManager.init();

    $('#pending').on('click', () => {
        $('#filterStatus').val('Pending');
        ProductionManager.loadBatches();
    });

    $('#delayed').on('click', () => {
        $('#filterStatus').val('Delayed');
        ProductionManager.loadBatches();
    });
});

const ProductionManager = {
    currentPage: 1,
    perPage: 10,
    
    init: function() {
        this.loadBatches();
        this.loadDropdownData();
        this.setupEventListeners();
    },
    
    setupEventListeners: function() {
        $('#batchForm').on('submit', (e) => this.submitBatchForm(e));
        $('#addProductForm').on('submit', (e) => this.addProduct(e));
        $('#addCategoryForm').on('submit', (e) => this.addCategory(e));
        $('#addQualityForm').on('submit', (e) => this.addQuality(e));
        $('#costForm').on('submit', (e) => this.completeBatchWithCost(e));
        
        let searchTimeout;
        $('#searchInput').on('keyup', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.loadBatches();
            }, 300);
        });

        $('#filterStatus').on('change', () => this.loadBatches());
        
        $(document).on('click', '.btn-edit', (e) => {
            const batchId = $(e.currentTarget).closest('tr').data('batch-id');
            this.editBatch(batchId);
        });
        
        $(document).on('click', '.btn-delete', (e) => {
            e.stopPropagation();
            const $target = $(e.currentTarget);
            const $row = $target.closest('tr');
            
            if ($row.data('batch-id')) {
                const batchId = $row.data('batch-id');
                this.showDeleteConfirmation('batch', batchId);
            } else if ($target.data('product-id')) {
                const productId = $target.data('product-id');
                const productName = $target.data('product-name') || 'Product';
                this.showDeleteConfirmation('product', productId, productName);
            } else if ($target.data('category-id')) {
                const categoryId = $target.data('category-id');
                const categoryName = $target.data('category-name') || 'Category';
                this.showDeleteConfirmation('category', categoryId, categoryName);
            } else if ($target.data('quality-id')) {
                const qualityId = $target.data('quality-id');
                const qualityName = $target.data('quality-name') || 'Quality';
                this.showDeleteConfirmation('quality', qualityId, qualityName);
            }
        });
        
        $(document).on('click', '.btn-complete', function() {
            const batchId = $(this).closest('tr').data('batch-id');
            ProductionManager.openCostModal(batchId);
        });
    },

    openCostModal: function(batchId) {
        $('#cost_batch_id').val(batchId);
        $('#costForm')[0].reset();
        ModalManager.open('costModal');
    },

    completeBatchWithCost: function(e) {
        e.preventDefault();
        
        const batchId = $('#cost_batch_id').val();
        const formData = {
            labor_cost: $('#cost_labor_cost').val(),
            other_expenses: $('#cost_other_expenses').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        if (!formData.labor_cost || formData.labor_cost.trim() === '') {
            this.showErrorToast('Labor cost is required');
            $('#cost_labor_cost').focus();
            return;
        }
        
        if (!formData.other_expenses || formData.other_expenses.trim() === '') {
            this.showErrorToast('Other expenses is required');
            $('#cost_other_expenses').focus();
            return;
        }
        
        const submitBtn = $('#costForm button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Processing...');
        
        $.ajax({
            url: `/admin/completebatch/${batchId}`,
            method: 'POST',
            data: formData,
            dataType: 'json',
            headers: { 
                'X-CSRF-TOKEN': formData._token,
                'Accept': 'application/json'
            },
            success: (response) => {
                if (response.success) {
                    this.showSuccessToast(response.message || 'Batch completed successfully!');
                    ModalManager.close('costModal');
                    this.loadBatches();
                } else {
                    this.showErrorToast(response.message || 'Failed to complete batch');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: (xhr) => {
                let errorMessage = 'Failed to complete batch';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join(', ');
                    }
                }
                
                this.showErrorToast(errorMessage);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    },

    loadBatches: function(page = 1) {
        const search = $('#searchInput').val();
        const status = $('#filterStatus').val();
        const perPage = 10;

        this.showLoader('#batchBody');

        const statusParam = status === 'all' ? '' : status;

        $.ajax({
            url: '/admin/production',
            method: 'GET',
            data: { search, status: statusParam, page, per_page: perPage, ajax: true },
            success: (response) => {
                if (response.success) {
                    this.currentPage = response.pagination.current_page;
                    this.perPage = response.pagination.per_page;

                    const paginatedData = {
                        current_page: response.pagination.current_page,
                        last_page: response.pagination.last_page,
                        per_page: response.pagination.per_page,
                        total: response.pagination.total,
                        data: response.batches
                    };

                    this.renderBatches(paginatedData);

                    if (response.summary) {
                        this.updateSummaryCounts(response.summary);
                    }
                }
            },
            error: () => this.showError('#batchBody', 'Failed to load data')
        });
    },

    renderBatches: function(paginatedData) {
        const batches = paginatedData.data || paginatedData.batches || paginatedData;
        
        if (!batches || batches.length === 0) {
            $('#batchBody').html(this.emptyStateTemplate('No batches found', 'bi-inbox'));
            $('#batchPagination').empty();
            return;
        }
        
        const html = batches.map((batch, index) => this.batchRowTemplate(batch, index)).join('');
        $('#batchBody').html(html);

        if (paginatedData.current_page !== undefined) {
            this.renderPagination(paginatedData);
        } else if (paginatedData.pagination) {
            this.renderPagination(paginatedData.pagination);
        }
    },

    renderPagination: function(paginatedData) {
        const currentPage = paginatedData.current_page || 1;
        const lastPage = paginatedData.last_page || 1;
        const $pagination = $('#batchPagination');
        
        $pagination.empty();

        if (lastPage <= 1) return;

        let paginationHtml = '';

        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">&laquo; Previous</a>
            </li>
        `;

        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(lastPage, startPage + maxVisible - 1);
        
        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        if (startPage > 1) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="1">1</a>
                </li>
            `;
            if (startPage > 2) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${lastPage}">${lastPage}</a>
                </li>
            `;
        }

        paginationHtml += `
            <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">Next &raquo;</a>
            </li>
        `;

        $pagination.html(paginationHtml);

        $pagination.find('a.page-link').off('click').on('click', (e) => {
            e.preventDefault();
            const page = $(e.currentTarget).data('page');
            if (page && page > 0 && page <= lastPage && page !== currentPage) {
                this.loadBatches(page);
            }
        });
    },

    batchRowTemplate: function(batch, index) {
        const status = batch.status;
        const statusClass = status.toLowerCase().replace(/\s+/g, '-');
        const startDate = this.formatDate(batch.start_date);
        const completionDate = this.formatDate(batch.expected_completion);
        const isEditable = status === 'Pending' || status === 'Delayed';
        const sn = (this.currentPage - 1) * this.perPage + index + 1;

        return `
            <tr data-batch-id="${batch.id}">
                <td>${sn}</td>
                <td>${batch.product?.name || 'N/A'}</td>
                <td>${batch.product?.category?.name || 'N/A'}</td>
                <td>${batch.product?.quality?.name || 'N/A'}</td>
                <td>${batch.leader_name}</td>
                <td>${batch.quantity}</td>
                <td>${batch.expected_unit_cost ? 'NPR ' + this.formatCurrency(batch.expected_unit_cost) : '-'}</td>
                <td>${batch.total_cost ? 'NPR ' + this.formatCurrency(batch.total_cost) : '-'}</td>
                <td>${startDate}</td>
                <td>${completionDate}</td>
                <td>
                    <span class="status-badge status-${statusClass}">
                        ${status}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        ${isEditable ? `
                            <button class="btn-sm btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn-sm btn-complete" title="Mark Complete">
                                <i class="bi bi-check-circle"></i>
                            </button>
                        ` : ''}
                        <button class="btn-sm btn-delete" title="Delete" data-batch-id="${batch.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    },

    formatCurrency: function(amount) {
        return parseFloat(amount || 0).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    formatDate: function(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
    },

    updateSummaryCounts: function(summary) {
        $('#totalBatches').text(summary.totalBatches || 0);
        $('#pending').text(summary.pending || 0);
        $('#delayed').text(summary.delayed || 0);
        $('#savedProducts').text(summary.savedProducts || 0);
    },

    loadDropdownData: function() {
        this.loadProducts();
        this.loadCategories();
        this.loadQualities();
    },

    loadProducts: function() {
        $.ajax({
            url: '/admin/batchproducts',
            method: 'GET',
            success: (data) => {
                this.updateSelectOptions('#batchproduct_id', data, 'Select Product');
                
                const html = data.map((product, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${product.name}</td>
                        <td>${product.material_cost || '-'}</td>
                        <td>
                            <a href="/admin/selectbatch/${product.id}" class="btn-sm btn-modify">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn-sm btn-delete" 
                                    data-product-id="${product.id}" 
                                    data-product-name="${product.name.replace(/"/g, '&quot;')}"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
                $('#productsTableBody').html(html);
            }
        });
    },

    loadCategories: function() {
        $.ajax({
            url: '/admin/productcategories',
            method: 'GET',
            success: (data) => {
                this.updateSelectOptions('#productcategory_id', data, 'Select Category');
                
                const html = data.map((category, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${category.name}</td>
                        <td>
                            <button class="btn-sm btn-delete" 
                                    data-category-id="${category.id}" 
                                    data-category-name="${category.name.replace(/"/g, '&quot;')}"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
                $('#categoriesTableBody').html(html);
            }
        });
    },

    loadQualities: function() {
        $.ajax({
            url: '/admin/productqualities',
            method: 'GET',
            success: (data) => {
                this.updateSelectOptions('#productquality_id', data, 'Select Quality');
                
                const html = data.map((quality, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${quality.name}</td>
                        <td>
                            <button class="btn-sm btn-delete" 
                                    data-quality-id="${quality.id}" 
                                    data-quality-name="${quality.name.replace(/"/g, '&quot;')}"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
                $('#qualitiesTableBody').html(html);
            }
        });
    },

    updateSelectOptions: function(selector, data, defaultText) {
        const $select = $(selector);
        const currentValue = $select.val();
        
        $select.empty().append(`<option value="">${defaultText}</option>`);
        
        if (data && data.length > 0) {
            data.forEach(item => {
                $select.append(`<option value="${item.id}">${item.name}</option>`);
            });
            
            if (currentValue && data.some(item => item.id == currentValue)) {
                $select.val(currentValue);
            }
        }
    },

    addCategory: function(e) {
        e.preventDefault();
        const formData = $('#addCategoryForm').serialize();
        const submitBtn = $('#addCategoryForm button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Adding...');
        
        $.ajax({
            url: '/admin/addproductcategory',
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: (response) => {
                if (response.success) {
                    this.loadCategories();
                    $('#addCategoryForm')[0].reset();
                    this.showSuccessToast('Category added successfully!');
                    this.switchToManageTab('addCategoryModal', 'manageCategoriesTab');
                } else {
                    this.showErrorToast(response.message || 'Failed to add category');
                }
                submitBtn.prop('disabled', false).html(originalText);
            },
            error: (xhr) => {
                let errorMessage = 'Failed to add category';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join(', ');
                    }
                }
                
                this.showErrorToast(errorMessage);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    },

    addQuality: function(e) {
        e.preventDefault();
        const formData = $('#addQualityForm').serialize();
        const submitBtn = $('#addQualityForm button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Adding...');
        
        $.ajax({
            url: '/admin/addquality',
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: (response) => {
                if (response.success) {
                    this.loadQualities();
                    $('#addQualityForm')[0].reset();
                    this.showSuccessToast('Quality added successfully!');
                    this.switchToManageTab('addQualityModal', 'manageQualitiesTab');
                } else {
                    this.showErrorToast(response.message || 'Failed to add quality');
                }
                submitBtn.prop('disabled', false).html(originalText);
            },
            error: (xhr) => {
                let errorMessage = 'Failed to add quality';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join(', ');
                    }
                }
                
                this.showErrorToast(errorMessage);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    },

    addProduct: function(e) {
        e.preventDefault();
        const formData = $('#addProductForm').serialize();
        const submitBtn = $('#addProductForm button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Adding...');
        
        $.ajax({
            url: '/admin/batchproducts',
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: (response) => {
                if (response.success) {
                    this.loadProducts();
                    $('#addProductForm')[0].reset();
                    this.showSuccessToast('Product added successfully!');
                    this.switchToManageTab('addProductModal', 'manageProductsTab');
                } else {
                    this.showErrorToast(response.message || 'Failed to add product');
                }
                submitBtn.prop('disabled', false).html(originalText);
            },
            error: (xhr) => {
                let errorMessage = 'Failed to add product';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join(', ');
                    }
                }
                
                this.showErrorToast(errorMessage);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    },

    switchToManageTab: function(modalId, tabId) {
        const modal = $(`#${modalId}`);
        modal.find('.tab-content').removeClass('active');
        modal.find('.tab-btn').removeClass('active');
        $(`#${tabId}`).addClass('active');
        modal.find('.tab-btn').last().addClass('active');
    },

    submitBatchForm: function(e) {
        e.preventDefault();
        const form = $('#batchForm');
        const batchId = $('#batch_id').val();
        
        let url = '/admin/addbatches';
        let httpMethod = 'POST';
        
        if (batchId) {
            url = `/admin/updatebatches/${batchId}`;
            httpMethod = 'POST';
        }
        
        const formData = form.serialize();
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> Processing...');
        
        $.ajax({
            url: url,
            method: httpMethod,
            data: formData,
            headers: { 
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: (response) => {
                if (response.success) {
                    ModalManager.close('batchModal');
                    this.loadBatches();
                    this.showSuccessToast(response.message || 'Batch saved successfully!');
                } else {
                    this.showDetailedError(response.message || 'Failed to save batch');
                }
                submitBtn.prop('disabled', false).html(originalText);
            },
            error: (xhr) => {
                let errorMessage = 'An error occurred while processing your request.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join(', ');
                    }
                }
                
                this.showDetailedError(errorMessage);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    },

    editBatch: function(batchId) {
        $.ajax({
            url: `/admin/batch-data/${batchId}`,
            method: 'GET',
            dataType: 'json',
            success: (response) => {
                if (response.success && response.batch) {
                    const batch = response.batch;
                    
                    $('#batch_id').val(batch.id);
                    $('#batchproduct_id').val(batch.batchproduct_id);
                    $('#leader_name').val(batch.leader_name);
                    $('#quantity').val(batch.quantity);
                    
                    if (batch.start_date) {
                        $('#start_date').val(batch.start_date.split('T')[0]);
                    }
                    if (batch.expected_completion) {
                        $('#expected_completion').val(batch.expected_completion.split('T')[0]);
                    }
                    
                    // Remove date validation for EDIT mode
                    $('#start_date').removeAttr('min');
                    $('#expected_completion').removeAttr('min');
                    
                    $('#modalTitle').text('Edit Batch');
                    $('#submitBtn').text('Update Batch');
                    $('#formMethod').val('PUT');
                    
                    ModalManager.open('batchModal');
                } else {
                    this.showErrorToast(response.message || 'Failed to load batch data');
                }
            },
            error: (xhr) => {
                let errorMessage = 'Failed to load batch data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                this.showErrorToast(errorMessage);
            }
        });
    },

    showDeleteConfirmation: function(type, id, name = '') {
        const config = {
            'batch': {
                title: 'Delete Batch?',
                text: 'Are you sure you want to delete this batch?',
                warning: 'This action cannot be undone.',
                url: `/admin/deletebatches/${id}`,
                successCallback: () => this.loadBatches(),
                successMessage: 'Batch deleted successfully!'
            },
            'product': {
                title: 'Delete Product?',
                text: `Are you sure you want to delete "${name}"?`,
                warning: 'This will affect all related batches.',
                url: `/admin/deletebatchproduct/${id}`,
                successCallback: () => this.loadProducts(),
                successMessage: 'Product deleted successfully!'
            },
            'category': {
                title: 'Delete Category?',
                text: `Are you sure you want to delete "${name}"?`,
                warning: 'This will affect all products and batches in this category.',
                url: `/admin/deletecategory/${id}`,
                successCallback: () => this.loadCategories(),
                successMessage: 'Category deleted successfully!'
            },
            'quality': {
                title: 'Delete Quality?',
                text: `Are you sure you want to delete "${name}"?`,
                warning: 'This will affect all products and batches with this quality.',
                url: `/admin/deletequality/${id}`,
                successCallback: () => this.loadQualities(),
                successMessage: 'Quality deleted successfully!'
            }
        };

        const itemConfig = config[type] || config.batch;
        
        Swal.fire({
            title: itemConfig.title,
            html: `<div style="text-align: left;">
                <p>${itemConfig.text}</p>
                <p class="text-danger" style="font-size: 0.9em; margin-top: 10px;">
                    <i class="bi bi-exclamation-triangle"></i> ${itemConfig.warning}
                </p>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: itemConfig.url,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    dataType: 'json'
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to delete');
                    }
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Delete failed: ${error.statusText || error.responseJSON?.message || 'Unknown error'}`
                    );
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: itemConfig.successMessage,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                
                itemConfig.successCallback();
            }
        });
    },

    showLoader: function(selector) {
        $(selector).html(this.loadingTemplate());
    },

    showError: function(selector, message) {
        $(selector).html(this.emptyStateTemplate(message, 'bi-exclamation-triangle'));
    },

    showDetailedError: function(errorMessage) {
        const modalHtml = `
            <div id="stockErrorModal" class="modal-overlay" style="display:block; z-index:9999;">
                <div class="modal-content" style="max-width: 700px;">
                    <div class="modal-header bg-danger text-white">
                        <h3><i class="bi bi-exclamation-triangle"></i> Insufficient Raw Materials</h3>
                        <button class="modal-close" onclick="closeModal('stockErrorModal')">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Cannot create batch due to insufficient raw materials.
                        </div>
                        <div class="stock-error-details" style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;">
                            <pre style="white-space: pre-wrap; font-family: inherit; margin: 0; font-size: 14px;">${errorMessage}</pre>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('stockErrorModal')">Close</button>
                        <button type="button" class="btn btn-primary" onclick="window.location.href='/admin/rawmaterials'">
                            <i class="bi bi-box"></i> Go to Inventory
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#stockErrorModal').remove();
        $('body').append(modalHtml);
    },

    showSuccessToast: function(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    },

    showErrorToast: function(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000
        });
    },

    loadingTemplate: function() {
        return `
            <tr class="empty-state">
                <td colspan="12" style="text-align:center; padding:20px; color:#6c757d;">
                    <i class="bi bi-arrow-repeat spin" style="font-size:2rem;"></i>
                    <p>Loading...</p>
                </td>
            </tr>
        `;
    },

    emptyStateTemplate: function(message, icon = 'bi-inbox') {
        return `
            <tr class="empty-state">
                <td colspan="12" style="text-align:center; padding:20px; color:#6c757d;">
                    <i class="bi ${icon}" style="font-size:2rem;"></i>
                    <p>${message}</p>
                </td>
            </tr>
        `;
    }
};

const ModalManager = {
    open: function(modalId) {
        $('#' + modalId).show();
        $('body').css('overflow', 'hidden');
    },

    close: function(modalId) {
        $('#' + modalId).hide();
        $('body').css('overflow', 'auto');
        
        if (modalId === 'batchModal') {
            // Remove date validation when closing modal
            $('#start_date').removeAttr('min');
            $('#expected_completion').removeAttr('min');
            
            $('#batchForm')[0].reset();
            $('#batch_id').val('');
            $('#modalTitle').text('Add New Batch');
            $('#submitBtn').text('Add Batch');
            $('#submitBtn').prop('disabled', false);
        } else if (modalId === 'addProductModal') {
            $('#addProductForm')[0].reset();
            $('#addProductForm button[type="submit"]').prop('disabled', false).text('Add Product');
        } else if (modalId === 'addCategoryModal') {
            $('#addCategoryForm')[0].reset();
            $('#addCategoryForm button[type="submit"]').prop('disabled', false).text('Add Category');
        } else if (modalId === 'addQualityModal') {
            $('#addQualityForm')[0].reset();
            $('#addQualityForm button[type="submit"]').prop('disabled', false).text('Add Quality');
        } else if (modalId === 'costModal') {
            $('#costForm')[0].reset();
            $('#cost_batch_id').val('');
            $('#costForm button[type="submit"]')
                .prop('disabled', false)
                .html('<i class="bi bi-check-circle"></i> Save & Complete Batch');
        }
    },

    switchTab: function(tabId, event) {
        const modal = $(event.target).closest('.modal-content');
        modal.find('.tab-content').removeClass('active');
        modal.find('.tab-btn').removeClass('active');
        $('#' + tabId).addClass('active');
        $(event.target).addClass('active');
    }
};

function openBatchModal(batchId = null) {
    if (batchId) {
        ProductionManager.editBatch(batchId);
    } else {
        // For ADD mode - set date validation
        const today = new Date().toISOString().split('T')[0];
        $('#start_date').attr('min', today);
        $('#expected_completion').attr('min', today);
        
        $('#modalTitle').text('Add New Batch');
        $('#submitBtn').text('Add Batch');
        $('#formMethod').val('POST');
        $('#batchForm')[0].reset();
        ModalManager.open('batchModal');
    }
}

function openModal(modalId) {
    if (modalId === 'addProductModal') ProductionManager.loadProducts();
    else if (modalId === 'addCategoryModal') ProductionManager.loadCategories();
    else if (modalId === 'addQualityModal') ProductionManager.loadQualities();
    
    ModalManager.open(modalId);
}

function closeModal(modalId) {
    ModalManager.close(modalId);
}

function switchTab(tabId) {
    ModalManager.switchTab(tabId, event);
}