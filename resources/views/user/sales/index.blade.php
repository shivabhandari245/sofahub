@extends('layouts.user')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-12 d-flex justify-content-between">
            <h1 class="h3 mb-0">Sales History</h1>
            <a href="{{ url('user/saleitems') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> Sold Items
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('sales.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by ID or Customer"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Reset</a>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('sales.create') }}" class="btn btn-success">
                        <i class="fas fa-cash-register"></i> New Sale
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
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
                    <tbody id="salesTable">
                        @forelse($sales as $sale)
                        <tr class="{{ $sale->returned ? 'table-danger' : '' }}">
                            <td>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $sale->date->format('M d, Y h:i A') }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td>{{ $sale->user->name ?? 'N/A' }}</td>
                            <td>{{ $sale->items->count() }}</td>
                            <td>{{ number_format($sale->subtotal, 2) }}</td>
                            <td>{{ number_format($sale->discount, 2) }}</td>
                            <td>{{ number_format($sale->afterdiscount, 2) }}</td>
                            <td>{{ number_format($sale->tax_amount, 2) }}</td>
                            <td><strong>{{ number_format($sale->total_amount, 2) }}</strong></td>
                            <td class="{{ $sale->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($sale->profit, 2) }}
                            </td>
                            <td>
                                {{ $sale->payment_method ? ucfirst($sale->payment_method) : 'N/A' }}
                                @if($sale->payment_remarks)
                                    <br><small>{{ $sale->payment_remarks }}</small>
                                @endif
                            </td>
                            <td>
                                @if($sale->returned)
                                    <span class="badge bg-danger">Returned</span>
                                @else
                                    <span class="badge bg-success">Completed</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('user.sales.show', $sale->id) }}" class="btn btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('sales.print', $sale->id) }}" class="btn btn-secondary" target="_blank" title="Print">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center py-4">No sales found.</td>
                        </tr>
                        @endforelse
                    </tbody>

                    <!-- Pagination -->
                    <tfoot>
                        <tr>
                            <td colspan="14">
                                <div class="d-flex justify-content-between align-items-center mt-3 px-3">
                                    <div class="text-muted" id="paginationInfo"></div>
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
                                    </nav>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const dateFrom = document.querySelector('input[name="date_from"]');
    const dateTo = document.querySelector('input[name="date_to"]');
    const tableBody = document.getElementById('salesTable');

    let debounceTimer = null;

  // Inside the validateDates() function, add this logic:
function validateDates() {
    const today = new Date().toISOString().split('T')[0];
    const maxDate = new Date();
    maxDate.setFullYear(maxDate.getFullYear() + 10);
    const maxDateStr = maxDate.toISOString().split('T')[0];
    
    // Set max dates to prevent future dates too far ahead
    if (dateFrom.value) {
        dateFrom.max = maxDateStr;
        if (dateFrom.value > maxDateStr) {
            showError('"Date From" cannot be in the distant future');
            return false;
        }
    }
    
    if (dateTo.value) {
        dateTo.max = maxDateStr;
        if (dateTo.value > maxDateStr) {
            showError('"Date To" cannot be in the distant future');
            return false;
        }
    }
    
    // Basic date range validation
    if (dateFrom.value && dateTo.value && dateTo.value < dateFrom.value) {
        showError('"Date To" cannot be earlier than "Date From"');
        return false;
    }
    
    // Prevent dates too far in the past (optional, adjust as needed)
    const minDate = new Date('2000-01-01').toISOString().split('T')[0];
    if (dateFrom.value && dateFrom.value < minDate) {
        showError('"Date From" cannot be earlier than year 2000');
        return false;
    }
    
    if (dateTo.value && dateTo.value < minDate) {
        showError('"Date To" cannot be earlier than year 2000');
        return false;
    }
    
    removeError();
    return true;
}

// You can also add date picker restrictions on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set max date to today + 10 years (reasonable future limit)
    const maxDate = new Date();
    maxDate.setFullYear(maxDate.getFullYear() + 10);
    const maxDateStr = maxDate.toISOString().split('T')[0];
    
    // Set min date to year 2000
    const minDate = '2000-01-01';
    
    if (dateFrom) {
        dateFrom.max = maxDateStr;
        dateFrom.min = minDate;
    }
    
    if (dateTo) {
        dateTo.max = maxDateStr;
        dateTo.min = minDate;
    }
    
    // Optional: Set dateTo max to today if not specified
    if (!dateTo.value) {
        dateTo.value = new Date().toISOString().split('T')[0];
    }
});

    function fetchSales() {
        if (!validateDates()) return;

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="14" class="text-center py-4">
                        <span class="spinner-border spinner-border-sm"></span> Loading...
                    </td>
                </tr>
            `;

            const params = new URLSearchParams({
                search: searchInput.value.trim(),
                date_from: dateFrom.value,
                date_to: dateTo.value
            });

            fetch(`{{ route('sales.index') }}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html;
                const newRows = temp.querySelector('#salesTable').innerHTML;
                tableBody.innerHTML = newRows || `
                    <tr>
                        <td colspan="14" class="text-center py-4">No sales found.</td>
                    </tr>
                `;
            });
        }, 400);
    }

    function showError(message) {
        let alert = document.getElementById('filterError');
        if (!alert) {
            alert = document.createElement('div');
            alert.id = 'filterError';
            alert.className = 'alert alert-danger mt-3';
            tableBody.closest('.card').prepend(alert);
        }
        alert.textContent = message;
    }

    function removeError() {
        const alert = document.getElementById('filterError');
        if (alert) alert.remove();
    }

    // Live filter events
    searchInput.addEventListener('keyup', fetchSales);
    dateFrom.addEventListener('change', fetchSales);
    dateTo.addEventListener('change', fetchSales);
});
</script>
@endpush
