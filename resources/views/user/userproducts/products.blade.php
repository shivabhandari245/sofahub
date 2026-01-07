@extends('layouts.user')

@section('title','Products Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/usercss/products.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
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
            </div>
        </form>

        <!-- TABLE -->
        <div id="productsTableContainer">
         <div class="table-responsive">
  <table id="productsTable" class="table table-hover table-bordered">

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

</div>
 </div>
    </div>
</div>
@endsection

@push('scripts')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
$(function () {

    let table = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false, 
        ajax: {
            url: "{{ route('user.products.index') }}",
            data: function (d) {
                d.search_value = $('input[name="search"]').val();
                d.status = $('select[name="status"]').val();
                d.source = $('select[name="source"]').val();
            }
        },
        columns: [
            { data: 'name' },
            { data: 'category' },
            { data: 'quality' },
            { data: 'quantity' },
            {
                data: 'cost_per_product',
                render: data => 'Rs ' + parseFloat(data).toFixed(2)
            },
            {
                data: 'total_cost',
                render: data => 'Rs ' + parseFloat(data).toFixed(2)
            },
            { data: 'showroom', orderable: false, searchable: false },
            { data: 'source' },
            { data: 'status', orderable: false, searchable: false }
        ]
    });

    
    $('input[name="search"]').on('keyup', function () {
        table.draw();
    });

    $('select[name="status"], select[name="source"]').on('change', function () {
        table.draw();
    });
});
</script>



@endpush