@extends('layouts.user')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/usercss/purchase.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="container">

    <!-- Purchase Form -->
    <div class="card">
        <h2>New Purchase Order</h2>

        <form id="purchaseForm">
            @csrf

            <div class="form-group">
                <label>Product Name</label>
                <input type="text" id="productName" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select id="productCategory" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                    <option value="__new__">Add New Category</option>
                </select>
                <input type="text" id="newCategoryInput" style="display:none;">
            </div>

            <div class="form-group">
                <label>Supplier</label>
                <select id="supplierName" required>
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                    @endforeach
                    <option value="__new__">Add New Supplier</option>
                </select>
                <input type="text" id="newSupplierInput" style="display:none;">
            </div>

            <div class="form-group">
                <label>Supplier Contact</label>
                <input type="text" id="supplierContact">
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" id="purchaseQuantity" min="1" required>
            </div>

            <div class="form-group">
                <label>Unit Cost</label>
                <input type="number" id="unitCost" min="1" required>
            </div>

            <div class="form-group">
                <label>Quality</label>
                <select id="productQuality">
                    <option value="">Select Quality</option>
                    <option value="Premium">Premium</option>
                    <option value="Standard">Standard</option>
                    <option value="Economy">Economy</option>
                </select>
            </div>

            <div class="form-group">
                <label>Delivery Date</label>
                <input type="date" id="deliveryDate">
            </div>

            <div class="purchase-summary">
                <h3>Summary</h3>
                <p>Quantity: <span id="summaryQuantity">0</span></p>
                <p>Unit Cost: <span id="summaryUnitCost">0 NPR</span></p>
                <p>Total Cost: <span id="summaryTotalCost">0 NPR</span></p>
            </div>

            <button type="submit" id="purchaseSubmitBtn" class="btn btn-success">
                Complete Purchase
            </button>
        </form>
    </div>

    <!-- Purchases Table -->
    <div class="card">
        <h2>Recent Purchases</h2>

        <table class="table" id="purchasesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Qty</th>
                    <th>Unit Cost</th>
                    <th>Total</th>
                    <th>Quality</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="10" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination info + buttons -->
        <div class="d-flex justify-content-between align-items-center mt-2">
            <div id="paginationInfo" class="text-muted"></div>
            <div id="pagination" class="pagination-container"></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {

    // CSRF
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Toggle new inputs
    $('#productCategory').change(function() {
        $('#newCategoryInput').toggle(this.value === '__new__');
    });

    $('#supplierName').change(function() {
        $('#newSupplierInput').toggle(this.value === '__new__');
    });

    // Update summary
    function updateSummary() {
        let qty = parseInt($('#purchaseQuantity').val()) || 0;
        let cost = parseFloat($('#unitCost').val()) || 0;

        $('#summaryQuantity').text(qty);
        $('#summaryUnitCost').text(cost + ' NPR');
        $('#summaryTotalCost').text((qty * cost).toFixed(2) + ' NPR');
    }

    $('#purchaseQuantity, #unitCost').on('input', updateSummary);

    // Submit purchase
    $('#purchaseForm').submit(function(e) {
        e.preventDefault();

        let category = $('#productCategory').val() === '__new__' ?
            $('#newCategoryInput').val() :
            $('#productCategory').val();

        let supplier = $('#supplierName').val() === '__new__' ?
            $('#newSupplierInput').val() :
            $('#supplierName').val();

        if (!category || !supplier) {
            Swal.fire('Warning', 'Category & Supplier required', 'warning');
            return;
        }

        $.post("{{ route('user.purchase.store') }}", {
            product_name: $('#productName').val(),
            category: category,
            supplier: supplier,
            supplier_contact: $('#supplierContact').val(),
            quantity: $('#purchaseQuantity').val(),
            unit_cost: $('#unitCost').val(),
            quality: $('#productQuality').val(),
            delivery_date: $('#deliveryDate').val()
        }, function(res) {
            Swal.fire('Success', res.message, 'success');
            $('#purchaseForm')[0].reset();
            updateSummary();
            loadPurchases();
        });
    });

    // Load purchases
    function loadPurchases(page = 1) {
        $.getJSON("{{ route('user.purchase.latest') }}?page=" + page, function(res) {

            let rows = '';
            if (!res.data.length) {
                rows = `<tr><td colspan="10" class="text-center">No purchases found</td></tr>`;
            } else {
                $.each(res.data, function(i, p) {
                    rows += `
                        <tr>
                            <td>${i + 1 + (res.current_page - 1) * 5}</td>
                            <td>${p.product_name}</td>
                            <td>${p.category}</td>
                            <td>${p.supplier_name ?? ''}</td>
                            <td>${p.quantity}</td>
                            <td>${p.unit_cost}</td>
                            <td>${(p.quantity * p.unit_cost).toFixed(2)}</td>
                            <td>${p.quality ?? ''}</td>
                            <td>${p.delivery_date ?? ''}</td>
                            <td><span class="badge bg-success">${p.status}</span></td>
                        </tr>`;
                });
            }

            $('#purchasesTable tbody').html(rows);

            // Showing X of Y (controller unchanged)
            let perPage = 5;
            let totalRecords = (res.last_page - 1) * perPage + res.data.length;
            $('#paginationInfo').text(`Showing ${res.data.length} of ${totalRecords}`);

            // Pagination buttons (CSS based)
            let pagination = '';
            for (let i = 1; i <= res.last_page; i++) {
                pagination += `
                    <button class="page-btn ${i === res.current_page ? 'btn-primary' : ''}"
                        data-page="${i}">
                        ${i}
                    </button>
                `;
            }
            $('#pagination').html(pagination);
        });
    }

    $('#pagination').on('click', '.page-btn', function() {
        loadPurchases($(this).data('page'));
    });

    loadPurchases();
});
</script>
@endpush