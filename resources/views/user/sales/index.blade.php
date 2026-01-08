@extends('layouts.user')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

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
#salesTable th,#salesTable td{
    font-size:.8rem;
    padding:4px 6px;
    white-space: nowrap;
}

/* DataTables search/filter input */
.dataTables_wrapper .dataTables_filter input{
    height:26px;
    font-size:.8rem;
}
.dataTables_wrapper .dt-buttons button{
    padding:3px 6px;
    font-size:.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px){
    #salesTable th, #salesTable td{
        font-size: .75rem;
        padding: 3px 4px;
    }
}
</style>
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
                <table id="salesTable" class="table table-hover table-bordered nowrap" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Sales #</th>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>

function formatIndianNumber(x) {
    if(x == null) return 'Rs 0.00';
    let parts = parseFloat(x).toFixed(2).split(".");
    let integerPart = parts[0];
    let lastThree = integerPart.slice(-3);
    let otherNumbers = integerPart.slice(0, -3);
    if(otherNumbers != '') lastThree = ',' + lastThree;
    let result = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + '.' + parts[1];
    return 'Rs ' + result;
}

$(document).ready(function() {

    let table = $('#salesTable').DataTable({
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
            { 
                data: 'id',
                render: function(data, type, row) {
                    return data.toString().padStart(8, '0'); 
                }
            },
           { 
    data: 'date',
    render: function(data) {
        if(!data) return '';
        const d = new Date(data);
        return d.toLocaleString('en-NP', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }
}
,
            { data: 'customer' },
            { data: 'user' },
            { data: 'items_count', searchable: false },
            { 
                data: 'subtotal',
                render: function(data){ return formatIndianNumber(data); }
            },
            { 
                data: 'discount',
                render: function(data){ return formatIndianNumber(data); }
            },
            { 
                data: 'afterdiscount',
                render: function(data){ return formatIndianNumber(data); }
            },
            { 
                data: 'tax_amount',
                render: function(data){ return formatIndianNumber(data); }
            },
            { 
                data: 'total_amount',
                render: function(data){ return formatIndianNumber(data); }
            },
            { 
                data: 'profit',
                render: function(data){ return formatIndianNumber(data); }
            },
            { data: 'payment_status', orderable: false, searchable: false },
            { data: 'status', orderable: false, searchable: false },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        responsive: true,
        dom: 'Bfrtip', // Buttons placement
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        columnDefs: [
            // Hide less important columns on small screens
            { targets: [4,5,6,7,8,10], className: 'd-none d-md-table-cell' }
        ],
        drawCallback: function() {
            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    });

    // Date filters
    $('#dateFrom, #dateTo').on('change', function() {
        table.draw();
    });

    // Reset filters
    $('#resetBtn').click(function() {
        $('#dateFrom').val('');
        $('#dateTo').val('');
        table.search('').columns().search('').draw();
    });
});
</script>
@endpush
