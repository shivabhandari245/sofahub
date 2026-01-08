@extends('layouts.user')
@section('title','Sale Items')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
    /* Backdrop */
    .bs-modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        display: none;
        z-index: 1055;
        overflow-x: hidden;
        overflow-y: auto;
    }

    /* Dialog */
    .bs-modal-dialog {
        max-width: 500px;
        margin: 1.75rem auto;
        pointer-events: none;
    }

    /* Content */
    .bs-modal-content {
        background-color: #fff;
        border-radius: .5rem;
        pointer-events: auto;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        animation: bsFade .2s ease-out;
    }

    /* Header */
    .bs-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .bs-modal-title {
        margin: 0;
        font-size: 1.25rem;
    }

    /* Close button */
    .bs-btn-close {
        background: none;
        border: 0;
        font-size: 1.5rem;
        cursor: pointer;
        opacity: .5;
    }
    .bs-btn-close:hover { opacity: .75; }

    /* Body */
    .bs-modal-body {
        padding: 1rem;
    }

    .bs-form-control {
        width: 100%;
        padding: .375rem .75rem;
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }
    .bs-form-control:focus {
        outline: none;
        border-color: #86b7fe;
        box-shadow: 0 0 0 .2rem rgba(13,110,253,.25);
    }

    /* Footer */
    .bs-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        padding: .75rem;
        border-top: 1px solid #dee2e6;
    }

    /* Animation */
    @keyframes bsFade {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>



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

<!-- Bootstrap-like Custom Modal -->
<div id="returnModal" class="bs-modal">
    <div class="bs-modal-dialog">
        <div class="bs-modal-content">

            <div class="bs-modal-header">
                <h5 class="bs-modal-title">Return Product</h5>
                <button type="button" id="closeReturnModal" class="bs-btn-close">&times;</button>
            </div>

            <form method="POST" id="returnForm">
                @csrf
                <div class="bs-modal-body">
                    <label class="form-label">Return Reason</label>
                    <textarea name="return_reason" class="bs-form-control" rows="3" required></textarea>
                </div>

                <div class="bs-modal-footer">
                    <button type="submit" id="returnSubmitBtn" class="btn btn-danger btn-sm">Confirm</button>
                    <button type="button" id="cancelReturnModal" class="btn btn-secondary btn-sm">Cancel</button>
                </div>
            </form>

        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {

    /* ================= DATATABLE ================= */
    const table = $('#saleItemsTable').DataTable({
        paging: true,
        searching: true,
        info: true,
        lengthChange: false,
        pageLength: 5,
        columnDefs: [
            { orderable: false, targets: 9 }
        ]
    });

    // Status filter
    $('#filterStatus').on('change', function () {
        table.column(8).search(this.value).draw();
    });

    /* ================= CUSTOM RETURN MODAL ================= */
    const modal = document.getElementById('returnModal');
    const returnForm = document.getElementById('returnForm');
    const returnSubmitBtn = document.getElementById('returnSubmitBtn');
    const closeBtn = document.getElementById('closeReturnModal');
    const cancelBtn = document.getElementById('cancelReturnModal');

    function openModal() {
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    // Open modal
    $('#saleItemsTable').on('click', '.return-btn', function() {
        const id = $(this).data('id');
        returnForm.action = `/user/saleitems/return/${id}`;
        returnForm.reset();
        returnSubmitBtn.disabled = false;
        returnSubmitBtn.innerText = 'Confirm';
        openModal();
    });

    // Close modal buttons
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Close when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    // Submit handler
    returnForm.addEventListener('submit', function() {
        returnSubmitBtn.disabled = true;
        returnSubmitBtn.innerText = 'Processing...';
    });

});
</script>

@endpush
