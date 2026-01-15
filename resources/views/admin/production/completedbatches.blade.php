@extends('layouts.admin')

@section('title', 'Completed Batches')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                Completed Production Batches
            </h5>
            <a href="{{ url('/admin/production') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Pending Batches
            </a>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0"
                               placeholder="Search completed batches...">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-success px-3 py-2">
                        <i class="bi bi-clock-history me-1"></i>
                        <span id="batchCount">0</span> batches completed
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Quality</th>
                            <th>Leader</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Start Date</th>
                            <th>Completed Date</th>
                            <th>Status</th>
                            <th class="text-center pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="batchBody">
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Pagination will be injected here -->
                    <div id="paginationControls" class="mt-3"></div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
    }
    
    .empty-state {
        padding: 3rem 0;
        text-align: center;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #dee2e6;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        let currentPage = 1;
        const perPage = 10;

        $(document).ready(function () {
            loadCompletedBatches();

            let searchTimer;
            $('#searchInput').on('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => loadCompletedBatches(1), 500);
            });
        });

        function loadCompletedBatches(page = 1) {
            currentPage = page;
            const search = $('#searchInput').val();

            $.ajax({
                url: '/admin/viewcompletedbatches',
                method: 'GET',
                data: { search, page, ajax: true },
                beforeSend: function() {
                    $('#batchBody').html(`
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    `);
                },
                success: function (res) {
                    if (!res.batches || res.batches.length === 0) {
                        $('#batchBody').html(`
                            <tr>
                                <td colspan="12" class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h5 class="mt-2 mb-3">No Completed Batches Found</h5>
                                    <p class="text-muted">All completed production batches will appear here</p>
                                </td>
                            </tr>
                        `);
                        $('#batchCount').text('0');
                        $('#paginationControls').html('');
                        return;
                    }

                    $('#batchCount').text(res.total);

                    let html = '';
                    let sn = (res.current_page - 1) * res.per_page + 1;

                    res.batches.forEach(batch => {
                        html += `
                            <tr class="batch-row" data-batch-id="${batch.id}">
                                <td class="ps-3 fw-medium">${sn++}</td>
                                <td>${batch.product?.name ?? '-'}</td>
                                <td>${batch.product?.category?.name ?? '-'}</td>
                                <td>${batch.product?.quality?.name ?? '-'}</td>
                                <td>${batch.leader_name}</td>
                                <td>${batch.quantity}</td>
                                <td>NPR ${Number(batch.expected_unit_cost).toFixed(2)}</td>
                                <td>NPR ${Number(batch.total_cost).toFixed(2)}</td>
                                <td>${formatDate(batch.start_date)}</td>
                                <td>${formatDate(batch.updated_at)}</td>
                                <td>
                                    <span class="status-badge bg-success bg-opacity-10 text-success">
                                        <i class="bi bi-check-circle me-1"></i> Completed
                                    </span>
                                </td>
                                <td class="text-center pe-3">
                                    <button class="btn btn-action btn-outline-danger btn-delete" 
                                            title="Delete Batch" data-batch-id="${batch.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    $('#batchBody').html(html);
                    renderPagination(res.current_page, res.last_page);
                },
                error: function () {
                    $('#batchBody').html(`
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="alert alert-danger" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Failed to load completed batches
                                </div>
                            </td>
                        </tr>
                    `);
                }
            });
        }

        // ==================== Pagination Buttons ====================
        function renderPagination(current, last) {
            if (last <= 1) {
                $('#paginationControls').html('');
                return;
            }

            let html = `<nav><ul class="pagination justify-content-center">`;

            html += `
                <li class="page-item ${current === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadCompletedBatches(${current - 1})">Prev</a>
                </li>
            `;

            for (let i = 1; i <= last; i++) {
                html += `
                    <li class="page-item ${i === current ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadCompletedBatches(${i})">${i}</a>
                    </li>
                `;
            }

            html += `
                <li class="page-item ${current === last ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadCompletedBatches(${current + 1})">Next</a>
                </li>
            `;

            html += `</ul></nav>`;

            $('#paginationControls').html(html);
        }

        // ==================== Helper ====================
        function formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });
        }

</script>
@endpush