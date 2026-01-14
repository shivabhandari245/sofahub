@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/product.css') }}">
@endpush

@section('content')
<div class="container">
    <div class="card">
        <div class="header">
            <h1>Products Management</h1>
            <div>

                <a href="{{ url('admin/dispatch') }}" class="btn btn-success" style="margin-left: 10px;">
                    <i class="fas fa-truck"></i> View Dispatches
                </a>
            </div>
        </div>
        <p>Manage your showroom's sofa inventory and stock levels</p>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-value" id="totalProducts">0</div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="availableProducts">0</div>
                <div class="stat-label">Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="lowStockProducts">0</div>
                <div class="stat-label">Low Stock</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="outOfStockProducts">0</div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>
    </div>
<!-- productlist -->
<div class="card">
    <div class="search-filter">
        <input type="text" id="searchProducts" placeholder="Search products..." />
        <select id="filterStatus">
            <option value="all">All Status</option>
            <option value="Available">Available</option>
            <option value="Low">Low Stock</option>
            <option value="Out of Stock">Out of Stock</option>
        </select>
        <select id="filterSource">
            <option value="all">All Sources</option>
            <option value="admin">From Admin</option>
            <option value="purchase">Purchased</option>
        </select>
    </div>

    <div class="table-responsive">
        <table id="productTable" class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Quality</th>
                    <th>Quantity</th>
                    <th>Cost</th>
                    <th>Total Cost</th>
                    <th>ShowRoom</th>
                    <th>Source</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin/product.js') }}"></script>
@endpush