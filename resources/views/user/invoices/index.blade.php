@extends('layouts.user')

@section('title', 'Invoices')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0 fw-semibold">My Invoices</h5>

        <div class="d-flex gap-2">
            <select id="monthsFilter" class="form-select form-select-sm">
                <option value="1" {{ $months==1?'selected':'' }}>1 Month</option>
                <option value="3" {{ $months==3?'selected':'' }}>3 Months</option>
                <option value="6" {{ $months==6?'selected':'' }}>6 Months</option>
                <option value="12" {{ $months==12?'selected':'' }}>12 Months</option>
            </select>

            <a href="{{ route('user.invoices.downloadAll', ['months'=>$months]) }}"
               class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf"></i>
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-2 mb-3">
        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-primary">
                <span>Invoices</span>
                <strong>{{ $totalInvoices }}</strong>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-success">
                <span>Revenue</span>
                <strong>RS {{ number_format($totalRevenue,2) }}</strong>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-info">
                <span>Tax</span>
                <strong>RS {{ number_format($totalTax,2) }}</strong>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="stat-card stat-warning">
                <span>Discount</span>
                <strong>RS {{ number_format($totalDiscount,2) }}</strong>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="stat-card stat-success">
                <span>Paid</span>
                <strong>RS {{ number_format($totalPaid,2) }}</strong>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="stat-card stat-danger">
                <span>Unpaid</span>
                <strong>RS {{ number_format($totalUnpaid,2) }}</strong>
            </div>
        </div>

        <div class="col-md-4 col-sm-12">
            <div class="stat-card stat-warning">
                <span>Partial</span>
                <strong>RS {{ number_format($totalPartial,2) }}</strong>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-2">
            <table id="invoicesTable" class="table table-sm table-bordered table-striped w-100">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Items</th>
                        <th>Discount</th>
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
@endsection

<style>
/* Compact stat cards */
.stat-card{
    background:#fff;
    border-radius:6px;
    padding:8px 12px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:.8rem;
    box-shadow:0 1px 4px rgba(0,0,0,.06);
}
.stat-card span{color:#6c757d}
.stat-card strong{font-size:.95rem}

/* Accent borders */
.stat-primary{border-left:4px solid #0d6efd}
.stat-success{border-left:4px solid #198754}
.stat-info{border-left:4px solid #0dcaf0}
.stat-warning{border-left:4px solid #ffc107}
.stat-danger{border-left:4px solid #dc3545}

/* Table compactness */
#invoicesTable th,#invoicesTable td{
    font-size:.8rem;
    padding:4px 6px;
}
.dataTables_wrapper .dataTables_filter input{
    height:26px;
    font-size:.8rem;
}
.dataTables_wrapper .dt-buttons button{
    padding:3px 6px;
    font-size:.75rem;
}
</style>

@push('scripts')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
$(document).ready(function(){

    const table = $('#invoicesTable').DataTable({
        processing: true,
        serverSide: true,
        searching:true,
        ajax: {
            url: "{{ route('user.invoices.datatables') }}",
            data: function(d){
                d.months = $('#monthsFilter').val(); // Send month filter
            }
        },
columns: [
    { data: 'id', name: 'id' , searchable:true},
    { data: 'date', name: 'date', searchable:true },
    { data: 'customer', name: 'customer', searchable:true },
    { data: 'cashier', name: 'cashier' },
    { data: 'items', name: 'items', orderable:false, searchable:false },
    { data: 'discount', name: 'discount' },
    { data: 'tax_amount', name: 'tax_amount' },
    { data: 'total_amount', name: 'total_amount' },
    { data: 'profitafterdiscount', name: 'profitafterdiscount' },
    { data: 'payment_method', name: 'payment_method', searchable:true },
    { data: 'payment_status', name: 'payment_status', orderable:false },
    { data: 'actions', name: 'actions', orderable:false, searchable:false }
],

      
        order: [[1,'desc']],
        responsive:true,
        dom:'Bfrtip',
        buttons:['excel','csv','print','pdf'],
        language: {
            search: "Search Invoice:"
        }
    });

    // Month filter
    $('#monthsFilter').change(function(){
        table.ajax.reload();
    });

});
</script>
@endpush
