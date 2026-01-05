@extends('layouts.admin')
@section('title', 'Stock Management')

@section('content')


@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/stock.css') }}" />
@endpush


<div class="card">
    <h2>Finished Goods Inventory</h2>
    <p>Track all finished products ready for dispatch or showroom delivery.</p>
</div>

<!-- Summary -->
<div class="summary">
    <div class="summary-card">
        <h3>Total Products</h3>
        <p id="totalProducts">0</p>
    </div>
    <div class="summary-card">
        <h3>Sufficient</h3>
        <p id="sufficientCount">0</p>
    </div>
    <div class="summary-card">
        <h3>Low</h3>
        <p id="lowCount">0</p>
    </div>
    <div class="summary-card">
        <h3>Critical</h3>
        <p id="criticalCount">0</p>
    </div>
</div>

<!-- Actions -->
<div class="top-actions">
    <button class="btn-add" id="toggleFormBtn">+ Add Product</button>
    <div class="search-filter">
        <input type="text" id="searchInput" placeholder="🔍 Search by product, category, or status" />
        <select id="filterStatus">
            <option value="all">All Status</option>
            <option value="Sufficient">Sufficient</option>
            <option value="Low">Low</option>
            <option value="Critical">Critical</option>
        </select>
    </div>
</div>

<!-- Add Stock Form -->
<div class="card add-form-container" id="addFormContainer">
    <h3>Add / Update Product</h3>
    <form id="stockForm">
        <div>
            <label>Product Name</label>
            <input type="text" id="productName" class="inline-input" required />
        </div>
        <div>
            <label>Category</label>
            <input type="text" id="category" class="inline-input" required />
        </div>
        <div>
            <label>Quantity</label>
            <input type="number" id="quantity" class="inline-input" required />
        </div>
        <div>
            <label>Unit</label>
            <input type="text" id="unit" class="inline-input" placeholder="e.g., Units, Sets" required />
        </div>
        <button type="submit" class="btn">Save Product</button>
    </form>
</div>

<!-- Stock Table -->
<div class="card">
    <h3>Stock Records</h3>
    <div class="table-container">
        <table id="stockTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="stockBody"></tbody>
        </table>
    </div>
</div>


@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {

    const toggleBtn = document.getElementById("toggleFormBtn");
    const formContainer = document.getElementById("addFormContainer");

    if (toggleBtn && formContainer) {
        toggleBtn.addEventListener("click", function() {
            formContainer.classList.toggle("active");

            if (formContainer.classList.contains("active")) {
                toggleBtn.textContent = "✖ Close Form";
            } else {
                toggleBtn.textContent = "+ Add Product";
            }
        });
    }

});
</script>
@endpush