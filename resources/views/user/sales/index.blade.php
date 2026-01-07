@extends('layouts.user')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid">

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search by ID or Customer">
                </div>
                <div class="col-md-2">
                    <input type="date" id="dateFrom" class="form-control" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" id="dateTo" class="form-control" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="button" id="resetBtn" class="btn btn-secondary">Reset</button>
                </div>
                <div class="col-md-3 text-end">
                                        <a href="{{ route('sales.create') }}" class="btn btn-success">
                        <i class="fas fa-cash-register"></i> New Sale
                    </a>
                    <a href="{{ url('user/saleitems') }}" class="btn btn-success">
                        <i class="fas fa-cash-list"></i> Sold Items
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="salesTable" class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Items</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>After Discount</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th>Profit</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    const table = $('#salesTable').DataTable({
        processing: true,
        serverSide: true,
          searching: false,
        ajax: {
            url: "{{ route('sales.index') }}",
            data: function(d) {
                d.search = $('#searchInput').val();
                d.date_from = $('#dateFrom').val();
                d.date_to = $('#dateTo').val();
            }
        },
        columns: [
            { data: 'id' },
            { data: 'date' },
            { data: 'customer' },
            { data: 'user' },
            { data: 'items_count', searchable: false },
            { data: 'subtotal', render: data => parseFloat(data).toFixed(2) },
            { data: 'discount', render: data => parseFloat(data).toFixed(2) },
            { data: 'afterdiscount', render: data => parseFloat(data).toFixed(2) },
            { data: 'tax_amount', render: data => parseFloat(data).toFixed(2) },
            { data: 'total_amount', render: data => parseFloat(data).toFixed(2) },
            { data: 'profit', render: data => parseFloat(data).toFixed(2) },
            { data: 'payment_method' },
            { data: 'status' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[1,'desc']],
        pageLength: 20,
        lengthMenu: [10, 20, 50, 100]
    });

   
    $('#searchInput, #dateFrom, #dateTo').on('keyup change', function() {
        table.draw();
    });

    // Reset button
    $('#resetBtn').click(function() {
        $('#searchInput').val('');
        $('#dateFrom').val('');
        $('#dateTo').val('');
        table.draw();
    });
});
</script>
@endpush
