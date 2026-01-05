@extends('layouts.user')

@section('title', 'Invoices')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Invoices</h4>
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" id="searchInvoices"
                                placeholder="Search by customer or invoice ID..." value="{{ request('search') }}">
                            <button class="btn btn-outline-primary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                            <a href="{{ route('user.invoices.index', ['months' => $months]) }}"
                                class="btn btn-outline-secondary" title="Clear search">
                                <i class="fas fa-times"></i>
                            </a>
                            @endif
                        </div>
                        <select id="monthsFilter" class="form-select" style="width: 150px;">
                            <option value="1" {{ $months == 1 ? 'selected' : '' }}>Last 1 Month</option>
                            <option value="3" {{ $months == 3 ? 'selected' : '' }}>Last 3 Months</option>
                            <option value="6" {{ $months == 6 ? 'selected' : '' }}>Last 6 Months</option>
                            <option value="12" {{ $months == 12 ? 'selected' : '' }}>Last 12 Months</option>
                        </select>
                        <a href="{{ route('user.invoices.downloadAll', ['months' => $months]) }}"
                            class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Invoices</h6>
                                    <h2>{{ $totalInvoices }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Revenue</h6>
                                    <h2>RS {{ number_format($totalRevenue, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Tax</h6>
                                    <h2>RS {{ number_format($totalTax, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Discount</h6>
                                    <h2>RS {{ number_format($totalDiscount, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Tax</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="invoicesTableBody">
                                @forelse($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->id }}</td>
                                    <td>{{ $invoice->formatted_date }}</td>
                                    <td>{{ $invoice->customer->name }}</td>
                                    <td>{{ $invoice->name }}</td>
                                    <td>{{ $invoice->quantity }}</td>
                                    <td>RS {{ $invoice->formatted_subtotal }}</td>
                                    <td>RS {{ $invoice->formatted_tax }}</td>
                                    <td>RS {{ $invoice->formatted_discount }}</td>
                                    <td><strong>RS {{ $invoice->formatted_total }}</strong></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-info view-invoice"
                                                data-id="{{ $invoice->id }}" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="{{ route('user.invoices.print', $invoice->id) }}"
                                                class="btn btn-sm btn-secondary" target="_blank" title="Print Invoice">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No invoices found</h5>
                                        <p class="text-muted">Your invoices will appear here after making sales</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <!-- ✅ TABLE PAGINATION -->
                            <tfoot>
                                <tr>
                                    <td colspan="12" class="bg-light">
                                        <div class="d-flex justify-content-between align-items-center px-3">
                                            <small class="text-muted">

                                                Showing {{ $invoices->count() }} of {{ $totalInvoices }} invoices

                                            </small>

                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@if ($invoices->count() >= 1)
<div class="pagination-container">
    <span class="page-btn btn-primary">1</span>


</div>
@endif


<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Invoice Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="invoiceModalBody">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printInvoiceBtn">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" class="btn btn-danger" id="downloadInvoiceBtn">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.invoice-actions .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    min-width: 32px;
}

.card-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.stats-card h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0;
}

.table td,
.table th {
    vertical-align: middle;
}

.btn-group {
    flex-wrap: nowrap;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Search invoices
    $('#searchBtn').click(function() {
        searchInvoices();
    });

    $('#searchInvoices').on('keyup', function(e) {
        if (e.key === 'Enter') {
            searchInvoices();
        }
    });

    // Months filter
    $('#monthsFilter').change(function() {
        const months = $(this).val();
        const searchQuery = $('#searchInvoices').val();
        let url = '{{ route("user.invoices.index") }}?months=' + months;
        if (searchQuery) {
            url += '&search=' + encodeURIComponent(searchQuery);
        }
        window.location.href = url;
    });

    // Load more invoices
    $('#loadMoreBtn').click(function(e) {
        e.preventDefault();
        loadAllInvoices();
    });

    // View invoice details
    $(document).on('click', '.view-invoice', function(e) {
        e.preventDefault();
        const invoiceId = $(this).data('id');
        loadInvoiceDetails(invoiceId);
    });

    // Print invoice from modal
    $('#printInvoiceBtn').click(function() {
        const invoiceId = $('#invoiceModal').data('invoice-id');
        if (invoiceId) {
            window.open('{{ url("user/invoices") }}/' + invoiceId + '/print', '_blank');
            $('#invoiceModal').modal('hide');
        }
    });

    // Download invoice PDF from modal
    $('#downloadInvoiceBtn').click(function() {
        const invoiceId = $('#invoiceModal').data('invoice-id');
        if (invoiceId) {
            window.location.href = '{{ url("user/invoices") }}/' + invoiceId + '/generate-pdf';
            $('#invoiceModal').modal('hide');
        }
    });

    function searchInvoices() {
        const query = $('#searchInvoices').val().trim();

        if (query.length < 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Search Error',
                text: 'Please enter at least 2 characters to search'
            });
            return;
        }

        // Show loading
        $('#searchBtn').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#searchBtn').prop('disabled', true);

        $.ajax({
            url: '{{ route("user.invoices.search") }}',
            type: 'GET',
            data: {
                q: query
            },
            success: function(invoices) {
                updateInvoicesTable(invoices);
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.status === 429 ? 'Too many requests. Please wait.' :
                        'Failed to search invoices'
                });
            },
            complete: function() {
                $('#searchBtn').html('<i class="fas fa-search"></i>');
                $('#searchBtn').prop('disabled', false);
            }
        });
    }

    function loadInvoiceDetails(invoiceId) {
        // Show loading in modal
        $('#invoiceModalBody').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading invoice details...</p>
            </div>
        `);
        $('#invoiceModal').modal('show');

        $.ajax({
            url: '{{ url("user/invoices/details") }}/' + invoiceId,
            type: 'GET',
            success: function(response) {
                displayInvoiceModal(response);
            },
            error: function(xhr) {
                $('#invoiceModalBody').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <h5 class="text-danger">Error loading invoice</h5>
                        <p class="text-muted">Failed to load invoice details. Please try again.</p>
                        <button class="btn btn-primary" onclick="loadInvoiceDetails(${invoiceId})">
                            <i class="fas fa-redo"></i> Retry
                        </button>
                    </div>
                `);
            }
        });
    }

    function displayInvoiceModal(data) {
        const invoice = data.invoice;
        const product = data.product;
        const formatted = data.formatted;

        let html = `
            <div class="invoice-details">
                <div class="invoice-header mb-4">
                    <h4 class="mb-1">Invoice #${invoice.id}</h4>
                    <p class="text-muted mb-0">${formatted.date}</p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Customer Details</h6>
                                <p class="mb-1"><strong>Name:</strong> ${invoice.customer_name}</p>
                                ${invoice.customer_email ? `<p class="mb-1"><strong>Email:</strong> ${invoice.customer_email}</p>` : ''}
                                ${invoice.customer_phone ? `<p class="mb-0"><strong>Phone:</strong> ${invoice.customer_phone}</p>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Product Details</h6>
                                <p class="mb-1"><strong>Product:</strong> ${product ? product.name : 'Unknown'}</p>
                                ${product && product.description ? `<p class="mb-0"><strong>Description:</strong> ${product.description.substring(0, 100)}${product.description.length > 100 ? '...' : ''}</p>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>${product ? product.name : 'Unknown Product'}</td>
                                <td class="text-center">${invoice.quantity}</td>
                                <td class="text-end">RS ${(invoice.subtotal / invoice.quantity).toFixed(2)}</td>
                                <td class="text-end">RS ${formatted.subtotal}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <td class="border-0"><strong>Subtotal:</strong></td>
                                    <td class="text-end border-0">RS ${formatted.subtotal}</td>
                                </tr>
                                <tr>
                                    <td class="border-0">Tax (${invoice.tax_rate || 0}%):</td>
                                    <td class="text-end border-0">RS ${formatted.tax_amount}</td>
                                </tr>
                                <tr>
                                    <td class="border-0">Discount:</td>
                                    <td class="text-end border-0">RS ${formatted.discount}</td>
                                </tr>
                                <tr class="table-active">
                                    <td><strong>Total Amount:</strong></td>
                                    <td class="text-end"><strong>RS ${formatted.total_amount}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#invoiceModalBody').html(html);
        $('#invoiceModal').data('invoice-id', invoice.id);
    }

    function updateInvoicesTable(invoices) {
        const tbody = $('#invoicesTableBody');

        if (invoices.length > 0) {
            let html = '';
            invoices.forEach(invoice => {
                html += `
                    <tr>
                        <td>#${invoice.id}</td>
                        <td>${invoice.formatted_date}</td>
                        <td>${invoice.customer_name}</td>
                        <td>${invoice.product_name}</td>
                        <td>${invoice.quantity}</td>
                        <td>RS ${invoice.formatted_subtotal || '0.00'}</td>
                        <td>RS ${invoice.formatted_tax || '0.00'}</td>
                        <td>RS ${invoice.formatted_discount || '0.00'}</td>
                        <td><strong>RS ${invoice.formatted_total || '0.00'}</strong></td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-info view-invoice" 
                                        data-id="${invoice.id}"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ url('user/invoices') }}/${invoice.id}/print" 
                                   class="btn btn-sm btn-secondary" 
                                   target="_blank"
                                   title="Print Invoice">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.html(html);

            // Update stats if needed
            if (invoices.length < $invoices.length) {
                $('#loadMoreBtn').hide();
            }
        } else {
            tbody.html(`
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No invoices found</h5>
                        <p class="text-muted">No invoices match your search criteria</p>
                        <a href="{{ route('user.invoices.index', ['months' => $months]) }}" 
                           class="btn btn-primary mt-2">
                            Clear Search
                        </a>
                    </td>
                </tr>
                 
            `);
        }
    }

    function loadAllInvoices() {
        const invoices = ('@json($allInvoices)');
        updateInvoicesTable(invoices);
        $('#loadMoreBtn').hide();
    }
});
</script>
@endpush