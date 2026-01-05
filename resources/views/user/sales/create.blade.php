@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Products Section -->
        <div class="col-xl-8 col-lg-7 col-md-12">
            <!-- Category Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="category" class="form-label fw-semibold">
                                <i class="fas fa-filter me-1"></i>Filter by Category
                            </label>
                            <select id="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->name }}">{{ $category->name }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="col-md-6">
                            <label for="productSearch" class="form-label fw-semibold">
                                <i class="fas fa-search me-1"></i>Search Products
                            </label>
                            <input type="text" id="productSearch" class="form-control"
                                placeholder="Search by product name, code or category...">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" id="refreshProductsBtn">
                                <i class="fas fa-sync-alt me-2"></i> Refresh Products
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes me-2"></i>Available Products
                    </h5>
                    <span class="badge bg-primary" id="productCount">0 products</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="productTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Product Name</th>
                                    <th>Category</th>
                                    <th>Quality</th>
                                    <th width="15%">Cost Price</th>
                                    <th width="15%">Stock</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">

                            </tbody>
                        </table>
                    </div>

                    <div id="productsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading products...</p>
                    </div>

                    <!-- Empty state -->
                    <div id="productsEmpty" class="text-center py-5" style="display: none;">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No products found</h5>
                        <p class="text-muted">Try adjusting your search or filter</p>
                    </div>

                    <!-- Pagination -->
                    <nav id="productPagination" class="my-3" style="display: none;">
                        <ul class="pagination justify-content-center" id="paginationList"></ul>
                    </nav>
                </div>
            </div>
        </div>
        <!-- End Products Section -->

        <!-- Cart Section -->
        <div class="col-xl-4 col-lg-5 col-md-12">
            <div class="card sticky-lg-top" style="top:20px;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Cart
                        <span class="badge bg-primary float-end" id="cartCount">0</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%">Product</th>
                                    <th style="width: 20%">Qty</th>
                                    <th style="width: 15%">Price</th>
                                    <th style="width: 15%">Subtotal</th>
                                    <th style="width: 15%">Profit</th>
                                    <th style="width: 5%"></th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody"></tbody>
                        </table>
                    </div>

                    <div class="p-3">
                        <form action="{{ route('sales.store') }}" method="POST" id="checkoutForm">
                            @csrf

                            <!-- Customer Section -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0"><i class="fas fa-user me-1"></i>Customer Details</h6>
                                </div>
                                <div class="card-body p-3">
                                    <!-- Customer Search Bar -->
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Search Customer</label>
                                        <div class="input-group">
                                            <input type="text" id="customerSearch" class="form-control"
                                                placeholder="Type customer name or phone..." autocomplete="off">
                                            <button type="button" class="btn btn-outline-primary" id="addCustomerBtn"
                                                title="Add New Customer">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <div id="customerSuggestions" class="position-absolute w-100"
                                            style="display: none; z-index: 1050;"></div>
                                    </div>

                                    <!-- Customer Details Form (Initially Hidden) -->
                                    <div id="customerDetailsForm" style="display: none;">
                                        <div class="border rounded p-3 bg-light mb-2">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0 fw-bold">
                                                    <i class="fas fa-user-check me-1 text-success"></i>
                                                    Selected Customer
                                                </h6>
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    id="changeCustomerBtn">
                                                    <i class="fas fa-exchange-alt me-1"></i>Change
                                                </button>
                                            </div>

                                            <!-- Customer Info Display -->
                                            <div id="customerInfoDisplay" class="mb-3"></div>

                                            <!-- Hidden Inputs -->
                                            <input type="hidden" id="customer_id" name="customer_id" required>
                                            <input type="hidden" id="customer_name" name="customer_name">
                                            <input type="hidden" id="customer_phone" name="customer_phone">
                                            <input type="hidden" id="customer_email" name="customer_email">
                                            <input type="hidden" id="customer_address" name="customer_address">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tax / Discount -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0"><i class="fas fa-percentage me-1"></i>Tax & Discount</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Tax %</label>
                                            <input type="number" id="taxRate" name="tax_rate" class="form-control"
                                                value="0" min="0" max="100" step="0.01">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Discount (RS)</label>
                                            <input type="number" id="discount" name="discount" class="form-control"
                                                value="0" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="card mb-3">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0"><i class="fas fa-calculator me-1"></i>Payment Summary</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="border rounded p-3 bg-white">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Subtotal:</span>
                                            <span class="fw-semibold" id="cartSubtotal">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Tax:</span>
                                            <span class="text-danger" id="taxAmount">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Discount:</span>
                                            <span class="text-success" id="discountAmount">0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between border-top pt-2">
                                            <span class="fw-bold fs-5">Total:</span>
                                            <span class="fw-bold fs-5 text-primary" id="cartTotal">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="cartItems" id="cartItemsInput">
                            <button type="submit" class="btn btn-success w-100 btn-lg" id="checkoutBtn" disabled>
                                <i class="fas fa-check-circle me-2"></i>Process Sale
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Cart Section -->
    </div>
</div>

<!-- Quantity Modal -->
<div class="modal fade" id="quantityModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productName"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="quantityInput" min="0.01" step="0.01" value="1">
                    <small class="text-muted">Available: <span id="availableStock" class="fw-semibold"></span></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Selling Price (RS)</label>
                    <input type="number" class="form-control" id="unitPriceInput" min="0.01" step="0.01" value="0">
                    <small class="text-muted">Cost Price: <span id="costPrice" class="fw-semibold"></span></small>
                </div>
                <div class="alert alert-info py-2">
                    <small>Profit per unit: <span id="profitPerUnit" class="fw-bold">0.00</span></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAdd">Add to Cart</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i> Add New Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="addCustomerForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user me-1"></i> Name *
                            </label>
                            <input type="text" class="form-control" id="modalCustomerName" name="name">
                            <small class="text-danger error-name"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-phone me-1"></i> Phone *
                            </label>
                            <input type="tel" class="form-control" id="modalCustomerPhone" name="phone">
                            <small class="text-danger error-phone"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-envelope me-1"></i> Email
                            </label>
                            <input type="email" class="form-control" id="modalCustomerEmail" name="email">
                            <small class="text-danger error-email"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i> Address
                            </label>
                            <textarea class="form-control" id="modalCustomerAddress" name="address"></textarea>
                            <small class="text-danger error-address"></small>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Customer
                            </button>
                        </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }

    .table-responsive {
        font-size: 0.875rem;
    }

    .btn-lg {
        padding: 0.75rem;
        font-size: 1rem;
    }

    .customer-details-grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }

    .card-header h5,
    .card-header h6 {
        font-size: 1rem;
    }
}

/* Customer suggestions styling */
#customerSuggestions {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 250px;
    overflow-y: auto;
}

.customer-suggestion-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f8f9fa;
    transition: all 0.2s;
    display: flex;
    align-items: center;
}

.customer-suggestion-item:hover {
    background-color: #f8f9fa;
}

.customer-suggestion-item:last-child {
    border-bottom: none;
}

.customer-suggestion-item.active {
    background-color: #e7f1ff;
    border-left: 3px solid #0d6efd;
}

.customer-suggestion-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #0d6efd;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    flex-shrink: 0;
}

.customer-suggestion-info {
    flex: 1;
    min-width: 0;
}

.customer-suggestion-name {
    font-weight: 500;
    color: #212529;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.customer-suggestion-details {
    font-size: 0.8rem;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Customer Details Display */
.customer-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.customer-detail-item {
    background: white;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.customer-detail-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 4px;
}

.customer-detail-value {
    font-size: 0.9rem;
    color: #212529;
    font-weight: 500;
    word-break: break-word;
}

.customer-detail-value.empty {
    color: #adb5bd;
    font-style: italic;
}

/* Cart item styling */
.cart-quantity {
    display: flex;
    width: 70px;
}

.remove-item {
    padding: 0.25rem 0.5rem;
    line-height: 1;
}

/* Animation for cart updates */
@keyframes highlight {
    0% {
        background-color: #d4edda;
    }

    100% {
        background-color: transparent;
    }
}

.highlight-row {
    animation: highlight 1s ease-out;
}

/* Fade in animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.3s ease-out;
}

/* Responsive table */
@media (max-width: 768px) {
    .cart-quantity {
        width: 60px;
        padding: 0.25rem;
    }

    .table th,
    .table td {
        padding: 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
let products = [];
let cart = [];
let currentProduct = null;

/* ===============================
   PRODUCT LOADING
================================ */
function loadProducts(page = 1) {
    $('#productsLoading').show();
    $('#productsEmpty').hide();

    $.get("{{ route('user.products.ajax') }}", {
        page: page,
        search: $('#productSearch').val(),
        category_id: $('#category').val()
    }, function(res) {
        $('#productsLoading').hide();
        $('#productTableBody').empty();

        if (res.data.length === 0) {
            $('#productsEmpty').show();
            $('#productCount').text('0 products');
            return;
        }

        $('#productCount').text(res.total + ' products');

        res.data.forEach(p => {
            $('#productTableBody').append(`
        <tr>
            <td>${p.name}</td>
          <td>${p.category}</td>
<td>${p.quality}</td>

            <td>${p.cost_per_product}</td>
            <td>${p.quantity}</td>
            <td>
                <button class="btn btn-sm btn-primary addProduct"
                    data-product='${JSON.stringify(p)}'>
                    Add
                </button>
            </td>
        </tr>
    `);
        });


        renderPagination(res);
    });
}

function renderPagination(res) {
    let html = '';
    for (let i = 1; i <= res.last_page; i++) {
        html += `
            <li class="page-item ${i === res.current_page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
    }
    $('#paginationList').html(html);
    $('#productPagination').show();
}

/* ===============================
   ADD PRODUCT MODAL
================================ */
$(document).on('click', '.addProduct', function() {
    currentProduct = $(this).data('product');

    $('#productName').text(currentProduct.name);
    $('#availableStock').text(currentProduct.quantity);
    $('#costPrice').text(currentProduct.cost_per_product);
    $('#quantityInput').val(1);
    $('#unitPriceInput').val(currentProduct.cost_per_product);
    $('#profitPerUnit').text('0.00');

    $('#quantityModal').modal('show');
});

$('#unitPriceInput, #quantityInput').on('input', function() {
    let profit = $('#unitPriceInput').val() - currentProduct.cost_per_product;
    $('#profitPerUnit').text(profit.toFixed(2));
});

$('#confirmAdd').click(function() {
    let qty = parseFloat($('#quantityInput').val());
    let price = parseFloat($('#unitPriceInput').val());

    cart.push({
        product_id: currentProduct.id,
        name: currentProduct.name,
        quantity: qty,
        unit_price: price,
        cost_price: currentProduct.cost_per_product
    });

    $('#quantityModal').modal('hide');
    renderCart();
});

/* ===============================
   CART RENDER
================================ */
function renderCart() {
    let tbody = $('#cartTableBody').empty();
    let subtotal = 0;
    let profit = 0;

    cart.forEach((item, index) => {
        let rowSubtotal = item.quantity * item.unit_price;
        let rowProfit = (item.unit_price - item.cost_price) * item.quantity;

        subtotal += rowSubtotal;
        profit += rowProfit;

        tbody.append(`
            <tr>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>${item.unit_price}</td>
                <td>${rowSubtotal.toFixed(2)}</td>
                <td>${rowProfit.toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-danger removeItem" data-index="${index}">
                        ×
                    </button>
                </td>
            </tr>
        `);
    });

    $('#cartSubtotal').text(subtotal.toFixed(2));
    calculateTotals();
    $('#cartItemsInput').val(JSON.stringify(cart));
    $('#checkoutBtn').prop('disabled', cart.length === 0);
    $('#cartCount').text(cart.length);
}

$(document).on('click', '.removeItem', function() {
    cart.splice($(this).data('index'), 1);
    renderCart();
});

/* ===============================
   TAX & DISCOUNT
================================ */
$('#taxRate, #discount').on('input', calculateTotals);

function calculateTotals() {
    let subtotal = parseFloat($('#cartSubtotal').text());
    let taxRate = parseFloat($('#taxRate').val()) || 0;
    let discount = parseFloat($('#discount').val()) || 0;

    let tax = subtotal * (taxRate / 100);
    let total = subtotal + tax - discount;

    $('#taxAmount').text(tax.toFixed(2));
    $('#discountAmount').text(discount.toFixed(2));
    $('#cartTotal').text(total.toFixed(2));
}

/* ===============================
   CUSTOMER SEARCH
================================ */
$('#customerSearch').on('keyup', function() {
    let q = $(this).val();
    if (!q) return $('#customerSuggestions').hide();

    $.get("{{ route('user.customers.search') }}", {
        q
    }, function(res) {
        let html = '';
        res.forEach(c => {
            html += `
                <div class="list-group-item customerSelect"
                     data-customer='${JSON.stringify(c)}'>
                    ${c.name} - ${c.phone}
                </div>`;
        });
        $('#customerSuggestions').html(html).show();
    });
});

$(document).on('click', '.customerSelect', function() {
    let c = $(this).data('customer');

    $('#customer_id').val(c.id);
    $('#customer_name').val(c.name);
    $('#customer_phone').val(c.phone);
    $('#customer_email').val(c.email);
    $('#customer_address').val(c.address);

    $('#customerInfoDisplay').html(`
        <strong>${c.name}</strong><br>
        ${c.phone}<br>${c.email ?? ''}
    `);

    $('#customerDetailsForm').show();
    $('#customerSuggestions').hide();
});


function validateCustomerForm() {
    let valid = true;

    // Clear old errors
    $('.error-name, .error-phone, .error-email, .error-address').text('');
    $('#addCustomerForm input, #addCustomerForm textarea').removeClass('is-invalid');

    let name = $('#modalCustomerName').val().trim();
    let phone = $('#modalCustomerPhone').val().trim();
    let email = $('#modalCustomerEmail').val().trim();

    // Name
    if (!name) {
        $('.error-name').text('Customer name is required');
        $('#modalCustomerName').addClass('is-invalid');
        valid = false;
    }

    // Phone
    if (!phone) {
        $('.error-phone').text('Phone number is required');
        $('#modalCustomerPhone').addClass('is-invalid');
        valid = false;
    } else if (phone.length < 6) {
        $('.error-phone').text('Phone number is too short');
        $('#modalCustomerPhone').addClass('is-invalid');
        valid = false;
    }

    // Email (optional)
    if (email) {
        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            $('.error-email').text('Enter a valid email address');
            $('#modalCustomerEmail').addClass('is-invalid');
            valid = false;
        }
    }

    return valid;
}

/* ===============================
   ADD CUSTOMER
================================ */
$('#addCustomerBtn').click(() => {
    $('#addCustomerForm')[0].reset();
    $('.text-danger').text('');
    $('#addCustomerForm input, #addCustomerForm textarea').removeClass('is-invalid');
    $('#addCustomerModal').modal('show');
});

$('#addCustomerForm').submit(function(e) {
    e.preventDefault();

    if (!validateCustomerForm()) {
        return;
    }

    $.post("{{ route('user.customers.store') }}", $(this).serialize())
        .done(function(res) {
            if (res.success) {
                $('#customer_id').val(res.customer.id);
                $('#customer_name').val(res.customer.name);
                $('#customer_phone').val(res.customer.phone);

                $('#customerInfoDisplay').html(
                    `<strong>${res.customer.name}</strong><br>${res.customer.phone}`
                );

                $('#customerDetailsForm').show();
                $('#addCustomerModal').modal('hide');
                $('#addCustomerForm')[0].reset();
            }
        })
        .fail(function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;

                if (errors.name) $('.error-name').text(errors.name[0]);
                if (errors.phone) $('.error-phone').text(errors.phone[0]);
                if (errors.email) $('.error-email').text(errors.email[0]);
                if (errors.address) $('.error-address').text(errors.address[0]);
            } else if (xhr.responseJSON?.customer_exists) {
                $('.error-email').text(xhr.responseJSON.message);
            } else {
                alert('Something went wrong. Please try again.');
            }
        });
});


/* ===============================
   INIT
================================ */
$(document).ready(() => loadProducts());
$('#refreshProductsBtn').click(() => loadProducts());
$('#category, #productSearch').on('change keyup', () => loadProducts());
$(document).on('click', '.page-link', function(e) {
    e.preventDefault();
    loadProducts($(this).data('page'));
});
</script>

@endpush