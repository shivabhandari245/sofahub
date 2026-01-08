@extends('layouts.user')

@section('title', 'Invoices')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid">
  
    <div class="row mb-3">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div class="pdf">
            <h4>My Invoices</h4>
            <div class="pdf">
                
                <select id="monthsFilter" class="form-select">
                    <option value="1" {{ $months==1?'selected':'' }}>Last 1 Month</option>
                    <option value="3" {{ $months==3?'selected':'' }}>Last 3 Months</option>
                    <option value="6" {{ $months==6?'selected':'' }}>Last 6 Months</option>
                    <option value="12" {{ $months==12?'selected':'' }}>Last 12 Months</option>
                </select>
                
                <a href="{{ route('user.invoices.downloadAll', ['months'=>$months]) }}" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white"><div class="card-body"><h6>Total Invoices</h6><h2>{{ $totalInvoices }}</h2></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white"><div class="card-body"><h6>Total Revenue</h6><h2>RS {{ number_format($totalRevenue,2) }}</h2></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white"><div class="card-body"><h6>Total Tax</h6><h2>RS {{ number_format($totalTax,2) }}</h2></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white"><div class="card-body"><h6>Total Discount</h6><h2>RS {{ number_format($totalDiscount,2) }}</h2></div></div>
        </div>
    </div>

    <!-- DataTable -->
    <div class="card">
        <div class="card-body">
            <table id="invoicesTable" class="table table-striped table-bordered">
                <thead>
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
                        <th>GrossProfit</th>
                        <th>ActualProfit</th>
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

.pdf{
    gap:2px;
}
#invoicesTable.dataTable th,
#invoicesTable.dataTable td {
    padding: 0.2rem 0.5rem;
    font-size: 0.85rem;
}
.dataTables_wrapper .dataTables_filter input {
    height: 20px;
    font-size: 0.85rem;
}
.dataTables_wrapper .dt-buttons button {
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
}
.table-sm th,
.table-sm td {
    vertical-align: middle;
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
        ajax: {
            url: "{{ route('user.invoices.datatables') }}",
            data: function(d){
                d.months = $('#monthsFilter').val(); // Send month filter
            }
        },
        columns:[
            {data:'id', name:'id'},
            {data:'date', name:'date'},
            {data:'customer', name:'customer'},
            {data:'cashier', name:'cashier'},
            {data:'items', name:'items', orderable:false, searchable:false},
            {data:'subtotal', name:'subtotal'},
            {data:'discount', name:'discount'},
            {data:'afterdiscount', name:'afterdiscount', orderable:false},
            {data:'tax_amount', name:'tax_amount'},
            {data:'total_amount', name:'total_amount'},
            {data:'profit', name:'profit', orderable:false, searchable:false},
             {data:'profitafterdiscount', name:'profitafterdiscount', orderable:false, searchable:false},
            {data:'payment_method', name:'payment_method'},
            {data:'status', name:'status', orderable:false, searchable:false},
            {data:'actions', name:'actions', orderable:false, searchable:false},
        ],
        order: [[1,'desc']],
        responsive:true,
        dom:'Bfrtip',
        buttons:['excel','csv','print'],
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
