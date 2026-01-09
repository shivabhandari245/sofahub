@extends('layouts.user')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid">

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="dateFrom" class="form-label">From Date</label>
                    <input type="date" id="dateFrom" class="form-control" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label for="dateTo" class="form-label">To Date</label>
                    <input type="date" id="dateTo" class="form-control" max="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
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
                       <th>ID</th>
<th>Date</th>
<th>Customer</th>
<th>Cashier</th>
<th>Items</th>
<th>Subtotal</th>
<th>Discount</th>
<th>Tax</th>
<th>Total</th>
<th>Profit</th>
<th>Payment</th>
<th>Payment Status</th>
<th>Status</th>
<th>Actions</th>

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
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
let table;

$(document).ready(function() {

    // Initialize DataTable only once
    if ($.fn.DataTable.isDataTable('#salesTable')) {
        table = $('#salesTable').DataTable();
    } else {
        table = $('#salesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('sales.index') }}",
                data: function(d) {
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
    { data: 'subtotal' },
    { data: 'discount' },
    { data: 'tax_amount' },
    { data: 'total_amount' },
    { data: 'profit' },
    { data: 'payment_method' },
    { data: 'payment_status' },
    { data: 'status' },
    { data: 'actions', orderable: false, searchable: false }
],

            order: [[1,'desc']],
            pageLength: 20,
            lengthMenu: [10, 20, 50, 100],
            responsive: true,
            searching: true // enable built-in search
        });
    }

    // Date filters
    $('#dateFrom, #dateTo').on('change', function() {
        table.draw();
    });

    // Reset filters and search
    $('#resetBtn').click(function() {
        $('#dateFrom').val('');
        $('#dateTo').val('');
        table.search('').columns().search('').draw();
    });

});
</script>
@endpush
