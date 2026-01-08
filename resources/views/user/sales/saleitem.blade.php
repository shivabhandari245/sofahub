@extends('layouts.user')


@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="container mt-3">

    <div class="card mt-3 p-3">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0">Sale Items Records</h1>
            <a href="{{ url('user/sales') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <p class="text-muted mb-0">View all sold products with quantity, subtotal, and profit.</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="card mt-3 p-3">
        <!-- Optional Status Filter -->
        <div class="mb-2">
            <label>Status Filter:</label>
            <select id="filterStatus" class="form-select w-auto d-inline-block">
                <option value="">All</option>
                <option value="sold">Sold</option>
                <option value="partially_returned">Partially Returned</option>
                <option value="returned">Returned</option>
            </select>
        </div>

        <div class="table-responsive">
            <table id="saleItemsTable" class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Product ID</th>
                        <th>Sale ID</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                        <th>Profit</th>
                        <th>Sold Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($saleItems as $item)
                    <tr>
                        <td>{{ $item->product->id ?? '-' }}</td>
                        <td>#{{ $item->sale->id ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rs {{ number_format($item->unit_price, 2) }}</td>
                        <td>Rs {{ number_format($item->subtotal, 2) }}</td>
                        <td>Rs {{ number_format($item->profit, 2) }}</td>
                        <td>{{ $item->created_at->format('d M Y') }}</td>
                        <td>{{ $item->status }}</td>
                        <td>
                            @if(!$item->is_returned)
                            <button class="btn btn-warning btn-sm return-btn" data-id="{{ $item->id }}">
                                <i class="fas fa-undo"></i> Return
                            </button>
                            @else
                            <span class="badge bg-danger">Returned</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="returnForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Return Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Return Reason</label>
                <textarea name="return_reason" class="form-control" rows="3" required></textarea>
            </div>

            <div class="modal-footer">
                <button type="submit" id="returnSubmitBtn" class="btn btn-danger btn-sm">Confirm</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {

    // Initialize DataTable
    const table = $('#saleItemsTable').DataTable({
        paging: true,
        searching: true,
        info: true,
        lengthChange: false,
        pageLength: 5,
        columnDefs: [
            { orderable: false, targets: 9 } // Disable sorting on Actions
        ]
    });

    // Status filter
    $('#filterStatus').on('change', function () {
        table.column(8).search(this.value).draw(); // Status column index = 8
    });

    /* ================= RETURN MODAL ================= */
    const returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
    const returnForm = document.getElementById('returnForm');
    const returnSubmitBtn = document.getElementById('returnSubmitBtn');

    $('#saleItemsTable').on('click', '.return-btn', function() {
        const id = $(this).data('id');
        returnForm.action = `/user/saleitems/return/${id}`;
        returnForm.reset();
        returnSubmitBtn.disabled = false;
        returnSubmitBtn.innerText = 'Confirm';
        returnModal.show();
    });

    returnForm.addEventListener('submit', function() {
        returnSubmitBtn.disabled = true;
        returnSubmitBtn.innerText = 'Processing...';
    });
});
</script>
@endpush
