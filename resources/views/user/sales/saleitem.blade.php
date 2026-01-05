@extends('layouts.user')
@section('title','Sale Items')

@section('content')
<div class="container mt-3">

    <div class="card mt-3 p-3">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0">Sale Items Records</h1>
            <a href="{{ url('user/sales') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <p class="text-muted mb-0">View all sold products with quantity, subtotal and profit.</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success mt-3">{{ session('success') }}</div>
    @endif

    <div class="card mt-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">

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

                <tbody id="saleItemsTable">
                    @forelse($saleItems as $item)
                    <tr class="sale-row">
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
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">No sale items found.</td>
                    </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="10">
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted" id="paginationInfo" style="color:#2c3e50 ;"></div>
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

<!-- Return Modal -->
<div class=" modal fade" id="returnModal" tabindex="-1">
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
<script>
document.addEventListener('DOMContentLoaded', function() {

    /* ================= RETURN MODAL ================= */
    const returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
    const returnForm = document.getElementById('returnForm');
    const returnSubmitBtn = document.getElementById('returnSubmitBtn');

    document.querySelectorAll('.return-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            returnForm.action =
                `/user/saleitems/return/${btn.dataset.id}`;
            returnForm.reset();
            returnSubmitBtn.disabled = false;
            returnSubmitBtn.innerText = 'Confirm';
            returnModal.show();
        });
    });

    returnForm.addEventListener('submit', function() {
        returnSubmitBtn.disabled = true;
        returnSubmitBtn.innerText = 'Processing...';
    });

    /* ================= CLIENT PAGINATION ================= */
    const rowsPerPage = 5;
    const rows = document.querySelectorAll('.sale-row');
    const pagination = document.getElementById('pagination');
    const info = document.getElementById('paginationInfo');

    let currentPage = 1;
    const totalPages = Math.ceil(rows.length / rowsPerPage);

    function showPage(page) {
        currentPage = page;
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            row.style.display = index >= start && index < end ? '' : 'none';
        });

        info.innerText =
            `Showing ${Math.min(end, rows.length)} of ${rows.length} entries`;
        renderPagination();
    }

    function renderPagination() {
        pagination.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            pagination.innerHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); showPage(${i})">${i}</a>
                </li>
            `;
        }
    }

    window.showPage = showPage;
    showPage(1);
});
</script>
@endpush