@extends('layouts.user')

@section('title','Products Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/usercss/products.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="container">
    <div class="card">
        <div class="header d-flex justify-content-between align-items-center">
            <h1>Products Management</h1>
            <div>
                <a href="{{ url('user/purchase') }}" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Purchase Products
                </a>
                <a href="{{ url('user/dispatch') }}" class="btn btn-success ms-2">
                    <i class="fas fa-truck"></i> View Dispatches
                </a>
            </div>
        </div>

        <p>Manage your showroom's sofa inventory and stock levels</p>

        <!-- STATS (UNCHANGED) -->
        @php
        $totalProducts = \App\Models\ProductModel::where('user_id', auth()->id())->count();
        $availableProducts = \App\Models\ProductModel::where('user_id', auth()->id())->where('quantity','>',5)->count();
        $lowStockProducts = \App\Models\ProductModel::where('user_id',
        auth()->id())->whereBetween('quantity',[1,5])->count();
        $outOfStockProducts = \App\Models\ProductModel::where('user_id', auth()->id())->where('quantity',0)->count();
        @endphp

        <div class="stats d-flex gap-3 my-3">
            <div class="stat-card">
                <div class="stat-value">{{ $totalProducts }}</div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $availableProducts }}</div>
                <div class="stat-label">Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $lowStockProducts }}</div>
                <div class="stat-label">Low Stock</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $outOfStockProducts }}</div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>

        <!-- FILTERS (UNCHANGED) -->
        <form id="filterForm" method="GET" action="{{ route('user.products.index') }}">
            <div class="d-flex gap-2 mb-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
                    class="form-control">

                <select name="status" class="form-select">
                    <option value="all">All Status</option>
                    <option value="Available" {{ request('status')=='Available'?'selected':'' }}>Available</option>
                    <option value="Low" {{ request('status')=='Low'?'selected':'' }}>Low Stock</option>
                    <option value="Out of Stock" {{ request('status')=='Out of Stock'?'selected':'' }}>Out of Stock
                    </option>
                </select>

                <select name="source" class="form-select">
                    <option value="all">All Sources</option>
                    <option value="admin" {{ request('source')=='admin'?'selected':'' }}>From Admin</option>
                    <option value="purchased" {{ request('source')=='purchased'?'selected':'' }}>Purchased</option>
                </select>

                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>

        <!-- TABLE -->
        <div id="productsTableContainer">
            @include('user.userproducts.partials.products-table')
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const tableContainer = document.getElementById('productsTableContainer');
    const filterForm = document.getElementById('filterForm');

    // FILTER SUBMIT (AJAX)
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchProducts(new FormData(filterForm));
    });

    // PAGINATION (AJAX ONLY TABLE)
    tableContainer.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            fetchProducts(null, link.href);
        }
    });

    // DELETE PRODUCT
    tableContainer.addEventListener('click', function(e) {
        const btn = e.target.closest('.delete-product');
        if (!btn) return;

        const productId = btn.dataset.id;

        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/user/products/${productId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Deleted!', data.message, 'success');
                            fetchProducts(new FormData(filterForm));
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    });
            }
        });
    });

    function fetchProducts(formData = null, url = null) {
        let fetchUrl = url ?? "{{ route('user.products.index') }}";

        if (formData) {
            fetchUrl += '?' + new URLSearchParams(formData).toString();
        }

        fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                tableContainer.innerHTML = html; // TABLE ONLY
            });
    }
});
</script>
@endpush