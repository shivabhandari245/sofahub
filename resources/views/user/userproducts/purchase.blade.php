@extends('layouts.user')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/usercss/purchase.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="container-fluid px-md-4 px-2 py-4">

    <!-- Header with Action Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-dark fw-bold">Purchase Management</h1>
            <p class="text-muted mb-0">Manage your purchases and inventory</p>
        </div>
        <button id="showFormBtn" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            New Purchase
        </button>
    </div>

    <!-- Purchase Form Card -->
    <div class="card mb-4" id="purchaseFormCard" style="display:none;">
        <div class="card-header bg-white border-0 py-3">
            <h2 id="formTitle" class="h4 mb-0 text-dark fw-bold">📝 New Purchase Order</h2>
        </div>
        <div class="card-body">
            <form id="purchaseForm">
                @csrf
                <input type="hidden" id="purchaseId">

                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label><i class="bi bi-box me-2"></i>Product Name</label>
                            <input type="text" id="productName" required class="form-control" placeholder="Enter product name">
                        </div>

                        <div class="form-group">
                            <label><i class="bi bi-tags me-2"></i>Category</label>
                            <select id="productCategory" required class="form-control">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                                <option value="__new__">➕ Add New Category</option>
                            </select>
                            <input type="text" id="newCategoryInput" class="form-control mt-2" style="display:none;" placeholder="Enter new category name">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="bi bi-building me-2"></i>Supplier</label>
                                    <select id="supplierName" required class="form-control">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier }}">{{ $supplier }}</option>
                                        @endforeach
                                        <option value="__new__">➕ Add New Supplier</option>
                                    </select>
                                    <input type="text" id="newSupplierInput" class="form-control mt-2" style="display:none;" placeholder="Enter supplier name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="bi bi-telephone me-2"></i>Supplier Contact</label>
                                    <input type="text" id="supplierContact" class="form-control" placeholder="Phone number">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-lg-6">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="bi bi-boxes me-2"></i>Quantity</label>
                                    <input type="number" id="purchaseQuantity" min="1" required class="form-control" placeholder="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="bi bi-currency-dollar me-2"></i>Unit Cost (NPR)</label>
                                    <input type="number" id="unitCost" min="0" required class="form-control" placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><i class="bi bi-star me-2"></i>Quality</label>
                            <select id="productQuality" class="form-control">
                                <option value="">Select Quality</option>
                                <option value="Premium">⭐ Premium</option>
                                <option value="Standard">⚡ Standard</option>
                                <option value="Economy">💰 Economy</option>
                            </select>
                        </div>
<div class="form-group">
    <label><i class="bi bi-calendar-check me-2"></i>Delivery Date</label>
    <input type="date" id="deliveryDate" class="form-control">
</div>

                        
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="purchase-summary">
                    <h5><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                    <p>Quantity: <span id="summaryQuantity">0</span></p>
                    <p>Unit Cost: <span id="summaryUnitCost">0 NPR</span></p>
                    <p>Total Cost: <span id="summaryTotalCost">0 NPR</span></p>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" id="purchaseSubmitBtn" class="btn btn-success px-4 py-2">
                        <i class="bi bi-check-circle me-2"></i>
                        Save Purchase
                    </button>
                    <button type="button" id="cancelFormBtn" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-x-circle me-2"></i>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Purchases Table Card -->
    <div class="card">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0 text-dark fw-bold">📦 Recent Purchases</h2>
                <div class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    <small>Last updated: {{ now()->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="purchasesTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">S.N</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Total</th>
                            <th>Quality</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
$(document).ready(function(){

   $('#deliveryDate').attr('min', new Date().toISOString().split('T')[0]);

    
    $('#cancelFormBtn').click(function(){
        $('#purchaseFormCard').slideUp(300);
        $('#purchaseForm')[0].reset();
    });
    // Show form toggle
    $('#showFormBtn').click(function(){
        $('#purchaseFormCard').slideToggle();
        $('#purchaseForm')[0].reset();
        $('#purchaseId').val('');
        $('#formTitle').text('New Purchase Order');
        $('#purchaseSubmitBtn').text('Save Purchase');
        $('#summaryQuantity').text('0');
        $('#summaryUnitCost').text('0 NPR');
        $('#summaryTotalCost').text('0 NPR');
    });

    // Toggle new category/supplier input
    $('#productCategory').change(function(){ $('#newCategoryInput').toggle(this.value==='__new__'); });
    $('#supplierName').change(function(){ $('#newSupplierInput').toggle(this.value==='__new__'); });

    // Update summary
    function updateSummary(){
        let qty = parseInt($('#purchaseQuantity').val()) || 0;
        let cost = parseFloat($('#unitCost').val()) || 0;
        $('#summaryQuantity').text(qty);
        $('#summaryUnitCost').text(cost.toFixed(2) + ' NPR');
        $('#summaryTotalCost').text((qty*cost).toFixed(2) + ' NPR');
    }
    $('#purchaseQuantity, #unitCost').on('input', updateSummary);

    $.ajaxSetup({ headers:{ 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Initialize DataTable
    let table = $('#purchasesTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('user.purchase.latest') }}",
        columns: [
            { data: 'DT_RowIndex', orderable:false, searchable:false },
            { data: 'product_name' },
            { data: 'category' },
            { data: 'supplier_name', defaultContent: '' },
            { data: 'quantity' },
            { data: 'unit_cost', render: $.fn.dataTable.render.number(',', '.', 2, 'NPR ') },
            { data: 'total', render: $.fn.dataTable.render.number(',', '.', 2, 'NPR ') },
            { data: 'quality', defaultContent: '' },
            { data: 'delivery_date' },
            { data: 'status' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row){
                    return `
                    <button class="btn btn-sm btn-primary edit-btn" data-id="${row.id}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}">Delete</button>
                    `;
                }
            }
        ],
        order: [[1,'desc']]
    });

    // Submit create/update
    $('#purchaseForm').submit(function(e){
        e.preventDefault();
        let purchaseId = $('#purchaseId').val();

        let data = {
            product_name: $('#productName').val(),
            category: $('#productCategory').val()==='__new__' ? $('#newCategoryInput').val() : $('#productCategory').val(),
            supplier_name: $('#supplierName').val()==='__new__' ? $('#newSupplierInput').val() : $('#supplierName').val(),
            supplier_contact: $('#supplierContact').val(),
            quantity: $('#purchaseQuantity').val(),
            unit_cost: $('#unitCost').val(),
            quality: $('#productQuality').val(),
            delivery_date: $('#deliveryDate').val(),
            status: 'Purchased'
        };

        let url = purchaseId ? `/user/purchase/${purchaseId}` : "{{ route('user.purchase.store') }}";
        let method = purchaseId ? 'PUT' : 'POST';

        $.ajax({ url, method, data })
        .done(res=>{
            Swal.fire('Success', res.message, 'success');
            $('#purchaseForm')[0].reset();
            $('#purchaseId').val('');
            $('#formTitle').text('New Purchase Order');
            $('#purchaseSubmitBtn').text('Save Purchase');
            updateSummary();
            table.ajax.reload(null,false);
        })
        .fail(()=>Swal.fire('Error','Operation failed','error'));
    });

   $('#purchasesTable').on('click','.edit-btn', function(){
    let id = $(this).data('id');
    $.get(`/user/purchases/${id}/edit`, function(data){

        $('#purchaseId').val(data.id);
        $('#formTitle').text('Edit Purchase');
        $('#purchaseSubmitBtn').text('Update Purchase');

        $('#productName').val(data.product_name);

        // Handle category
        if(!$('#productCategory option[value="'+data.category+'"]').length){
            $('#productCategory').append(`<option value="${data.category}" selected>${data.category}</option>`);
        } else {
            $('#productCategory').val(data.category);
        }

        // Handle supplier
        if(!$('#supplierName option[value="'+data.supplier_name+'"]').length){
            $('#supplierName').append(`<option value="${data.supplier_name}" selected>${data.supplier_name}</option>`);
        } else {
            $('#supplierName').val(data.supplier_name);
        }

        $('#supplierContact').val(data.supplier_contact);
        $('#purchaseQuantity').val(data.quantity);
        $('#unitCost').val(data.unit_cost);
        $('#productQuality').val(data.quality);
        $('#deliveryDate').val(data.delivery_date);

        updateSummary();
        $('#purchaseFormCard').slideDown();
    }).fail(function(err){
        Swal.fire('Error','Failed to fetch purchase data','error');
        console.log(err);
    });
});


    // Delete
    $('#purchasesTable').on('click','.delete-btn', function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This purchase will be deleted!",
            icon: 'warning',
            showCancelButton:true,
            confirmButtonText:'Yes, delete it!'
        }).then((result)=>{
            if(result.isConfirmed){
                $.ajax({ url:`/user/purchase/${id}`, method:'DELETE' })
                .done(res=>{ Swal.fire('Deleted!', res.message, 'success'); table.ajax.reload(null,false); })
                .fail(()=>Swal.fire('Error','Delete failed','error'));
            }
        });
    });

});
</script>
@endpush
