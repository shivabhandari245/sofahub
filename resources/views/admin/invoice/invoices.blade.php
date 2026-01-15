@extends('layouts.admin')

@section('title', 'Invoices Management')

@push('styles')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

 <style>
    /* Professional Stats Cards with Icons - EXACT SAME AS USER */
    .stats-card {
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .stats-card h6 {
        font-size: 0.85rem;
        color: #555;
        margin-bottom: 0.5rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .stats-card h2 {
        font-size: 1.4rem;
        margin-bottom: 0.25rem;
        color: #333;
    }

    .stats-card small {
        font-size: 0.75rem;
        color: #666;
    }

    /* Icon in card */
    .stats-card i {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 1.5rem;
        color: #301818ff;
    }

    /* Payment Status Accent Colors */
    .bg-paid {
        border-left: 4px solid #28a745;
    }

    .bg-unpaid {
        border-left: 4px solid #dc3545;
    }

    .bg-partial {
        border-left: 4px solid #ffc107;
    }

    /* Filters */
    .filter-group select,
    .filter-group .btn {
        height: calc(1.8em + 0.75rem + 2px);
        font-size: 0.875rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-card h2 {
            font-size: 1.2rem;
        }
        .stats-card h6 {
            font-size: 0.75rem;
        }
        .stats-card small {
            font-size: 0.65rem;
        }
        .stats-card i {
            font-size: 1.2rem;
            top: 10px;
            right: 10px;
        }
    }

    /* Table responsiveness */
    .table-responsive {
        overflow-x: auto;
    }

    /* Payment Status Badges */
    .badge-paid {
        background-color: #28a745;
        color: white;
    }
    
    .badge-unpaid {
        background-color: #dc3545;
        color: white;
    }
    
    .badge-pending {
        background-color: #ffc107;
        color: #212529;
    }

    /* Action buttons */
    .action-btns {
        display: flex;
        gap: 5px;
    }

    /* Status column width */
    .status-col {
        width: 100px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <!-- Header + Filters - EXACT SAME AS USER -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-0">Invoices Management</h4>
                <p class="mb-0 text-muted">Track, manage, and monitor all invoices</p>
            </div>

            <div class="d-flex gap-2 flex-wrap filter-group">
                <select id="monthsFilter" class="form-select form-select-sm">
                    <option value="1" {{ $months==1?'selected':'' }}>Last 1 Month</option>
                    <option value="3" {{ $months==3?'selected':'' }}>Last 3 Months</option>
                    <option value="6" {{ $months==6?'selected':'' }}>Last 6 Months</option>
                    <option value="12" {{ $months==12?'selected':'' }}>Last 12 Months</option>
                </select>

                <select id="paymentStatusFilter" class="form-select form-select-sm">
                    <option value="all">All Status</option>
                    <option value="Paid">Paid</option>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Pending">Partial Paid</option>
                </select>

                <a id="exportPdfBtn"
                   href="#"
                   class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards - EXACT SAME AS USER -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <i class="fas fa-file-invoice-dollar"></i>
                <h6>Total Invoices</h6>
                <h2>{{ $totalInvoices }}</h2>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <i class="fas fa-coins"></i>
                <h6>Total Revenue</h6>
                <h2>RS {{ number_format($totalRevenue, 2) }}</h2>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <i class="fas fa-percent"></i>
                <h6>Total Tax</h6>
                <h2>RS {{ number_format($totalTax, 2) }}</h2>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <i class="fas fa-tags"></i>
                <h6>Total Discount</h6>
                <h2>RS {{ number_format($totalDiscount, 2) }}</h2>
            </div>
        </div>
    </div>

    <!-- Payment Summary - EXACT SAME AS USER -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stats-card bg-paid">
                <i class="fas fa-check-circle"></i>
                <h6>Paid</h6>
                <h2>{{ $totalPaidCount }} invoices</h2>
                <small>RS {{ number_format($totalPaidAmount, 2) }}</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stats-card bg-unpaid">
                <i class="fas fa-times-circle"></i>
                <h6>Unpaid</h6>
                <h2>{{ $totalUnpaidCount }} invoices</h2>
                <small>RS {{ number_format($totalUnpaidAmount, 2) }}</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stats-card bg-partial">
                <i class="fas fa-adjust"></i>
                <h6>Partial Paid</h6>
                <h2>{{ $totalPartialCount }} invoices</h2>
                <small>
                    RS {{ number_format($totalPartialAmount, 2) }} received /
                    RS {{ number_format($totalPartialRemaining, 2) }} remaining
                </small>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <table id="invoicesTable" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Discount</th>
                        <th>Tax</th>
                        <th>Total</th>
                        <th>Actual Profit</th>
                        <th class="status-col">Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- jQuery -->

<script>
$(document).ready(function(){
    // Initialize DataTable
    const table = $('#invoicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('/admin/allinvoices') }}",
            data: function(d){
                d.months = $('#monthsFilter').val();
                d.payment_status = $('#paymentStatusFilter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'date', name: 'created_at' },
            { data: 'customer', name: 'customer' },
            { data: 'cashier', name: 'cashier' },
            { data: 'discount', name: 'discount', orderable: false },
            { data: 'tax_amount', name: 'tax_amount', orderable: false },
            { data: 'total_amount', name: 'total_amount', orderable: false },
            { data: 'profit', name: 'profit', orderable: false },
            { 
                data: 'status', 
                name: 'status',
                render: function(data, type, row) {
                    let badgeClass = 'badge-secondary';
                    switch(data) {
                        case 'Paid': badgeClass = 'badge-paid'; break;
                        case 'Unpaid': badgeClass = 'badge-unpaid'; break;
                        case 'Pending': badgeClass = 'badge-pending'; break;
                    }
                    return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                },
                orderable: false,
                searchable: false
            },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false,
                className: 'text-center'
            }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 20, 50, 100, 200],
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-sm btn-success',
                text: '<i class="fas fa-file-excel"></i> Excel'
            },
            {
                extend: 'csv',
                className: 'btn btn-sm btn-info',
                text: '<i class="fas fa-file-csv"></i> CSV'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-secondary',
                text: '<i class="fas fa-print"></i> Print'
            }
        ],
        language: {
            search: "Search Invoice:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ invoices",
            infoEmpty: "Showing 0 to 0 of 0 invoices",
            infoFiltered: "(filtered from _MAX_ total invoices)"
        }
    });

    // Reload table when filters change
    $('#monthsFilter, #paymentStatusFilter').change(function(){
        table.ajax.reload();
    });

    // Update PDF export link when filters change
    function updateExportLink() {
        const months = $('#monthsFilter').val();
        const status = $('#paymentStatusFilter').val();
        let url = "{{ url('/admin/download-all') }}?months=" + months;
        
        if (status && status !== 'all') {
            url += '&payment_status=' + status;
        }
        
        $('#exportPdfBtn').attr('href', url);
    }

    // Initialize export link
    updateExportLink();
    
    // Update export link when filters change
    $('#monthsFilter, #paymentStatusFilter').change(function(){
        updateExportLink();
    });
});
</script>
@endpush