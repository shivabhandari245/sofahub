@extends('layouts.admin')
@section('title', 'Raw Materials Inventory - SofaHub Admin')
@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/rawmaterials.css') }}" />
@endpush

<div class="card">
    <h2>Raw Material Inventory</h2>
    <p>Manage and monitor raw materials supplied to and used by the factory for sofa production.</p>
</div>

<!-- Summary Section -->
<div class="summary">
    <div class="summary-card">
        <h3>Total Materials</h3>
        <p id="totalMaterials">{{ $materials->count() }}</p>
    </div>
    <div class="summary-card">
        <h3>Low Stock (&lt;50)</h3>
        <p id="lowStock">{{ $lowStockCount ?? 0 }}</p>
    </div>
    <div class="summary-card">
        <h3>Categories</h3>
        <p id="categoryCount">{{ $materialCategories->count() }}</p>
    </div>
</div>

<!-- Header with Add Button -->
<div class="page-header">
    <div class="filters">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search by name or supplier" />
        </div>
        <select id="filterCategory" class="filter-select">
            <option value="all">All Categories</option>
            @foreach($materialCategories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <button class="btn-add" id="toggleFormBtn">
            <i class="bi bi-plus-circle"></i> Add Material
        </button>
    </div>
</div>
<!-- Add Material Modal -->
<div id="addMaterialModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="bi bi-plus-circle"></i> <span id="formTitle">Add New Material</span></h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="materialForm" method="POST">
            @csrf
            <input type="hidden" id="materialId" name="id">
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Material Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required
                            placeholder="e.g. Premium Foam, Oak Wood">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <div class="select-with-btn">
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($materialCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-icon" id="addCategoryBtn" title="Add New Category">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="supplier_id">Supplier</label>
                        <div class="select-with-btn">
                            <select id="supplier_id" name="supplier_id" class="form-control">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-icon" id="addSupplierBtn" title="Add New Supplier">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required
                            placeholder="Enter current stock" min="0">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="unit_id">Unit *</label>
                        <div class="select-with-btn">
                            <select id="unit_id" name="unit_id" class="form-control" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-icon" id="addUnitBtn" title="Add New Unit">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="unit_cost">Unit Cost (Rs.) *</label>
                        <input type="number" step="0.01" id="unit_cost" name="unit_cost" class="form-control" required
                            placeholder="e.g. 150.50" min="0">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="storage_location">Storage Location</label>
                        <input type="text" id="storage_location" name="storage_location" class="form-control"
                            placeholder="e.g. Warehouse A - Rack 4">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary closeMaterialModal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Add Material</button>
            </div>
        </form>
    </div>
</div>

<!-- Restock Modal -->
<div id="restockModalBackdrop" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="bi bi-arrow-repeat"></i> Restock Material</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>

        <div class="modal-body">
            <form id="restockForm">
                @csrf
                <input type="hidden" id="restock_material_id" name="material_id">

                <div class="form-group">
                    <label for="restock_material_name">Material Name</label>
                    <input type="text" id="restock_material_name" class="form-control" readonly>
                </div>

                <div class="form-group">
                    <label for="restock_available_quantity">Available Quantity</label>
                    <input type="text" id="restock_available_quantity" class="form-control" readonly>
                </div>

                <div class="form-group">
                    <label for="restock_quantity">Restock Quantity *</label>
                    <input type="number" id="restock_quantity" name="restock_quantity" class="form-control" min="1"
                        required placeholder="Enter quantity to add">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <label for="restock_unit_cost">Unit Cost (Rs.) *</label>
                    <input type="number" id="restock_unit_cost" name="unit_cost" class="form-control" step="0.01"
                        min="0" required placeholder="Enter current unit cost">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success" id="submitRestockBtn">Restock Material</button>
                    <button type="button" class="btn btn-secondary closeRestockModal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dynamic Modal for Category/Supplier/Unit -->
<div class="modal-backdrop" id="modalBackdrop" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Manage Items</h3>
            <button type="button" class="btn-close" id="closeModalBtn">&times;</button>
        </div>

        <div class="modal-body">
            <div class="existing-items-section">
                <h4>Existing <span id="listTitle"></span></h4>
                <div class="items-list" id="existingList">
                    <!-- Items will be loaded here -->
                </div>
            </div>

            <div class="add-item-section">
                <label for="newItemInput" id="inputLabel">New Item Name</label>
                <input type="text" id="newItemInput" class="form-control" placeholder="Enter new item name">
                <button id="saveItemBtn" class="btn btn-primary mt-2">Add New</button>
            </div>
        </div>
    </div>
</div>

<!-- Material Table -->
<div class="card">
    <div class="table-container">
        <table id="materialTable" class="data-table">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Material Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Unit Cost</th>
                    <th>Total Cost</th>
                    <th>Storage Location</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="materialBody">
                @foreach($materials as $material)
                <tr data-category="{{ $material->category_id }}" data-quantity="{{ $material->quantity }}"
                    data-material='@json($material)'>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $material->name }}</td>
                    <td>
                        <span class="badge badge-category">{{ $material->category->name ?? 'N/A' }}</span>
                    </td>
                    <td>{{ $material->supplier->name ?? 'N/A' }}</td>
                    <td>
                        <span class="quantity-display {{ $material->quantity < 50 ? 'low-stock' : '' }}">
                            {{ $material->quantity }}
                        </span>
                    </td>
                    <td>{{ $material->unit->name ?? 'N/A' }}</td>
                    <td>Rs. {{ number_format($material->unit_cost, 2) }}</td>
                    <td>Rs. {{ number_format($material->total_cost, 2) }}</td>
                    <td>{{ $material->storage_location ?? 'N/A' }}</td>
                    <td>
                        <div class="action-buttons">
                            <button type="button" class="btn-action btn-edit" title="Edit Material"
                                data-id="{{ $material->id }}" data-material='@json($material)'>
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <!-- View Button -->
                            <button type="button" class="btn-action btn-view" title="View History"
                                onclick="window.location='{{ url('/admin/viewmaterialhistory/'.$material->id) }}'">
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- Delete Button -->
                            <form action="{{ url('/admin/deleterawmaterials/' . $material->id) }}" method="POST"
                                class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-action btn-delete" title="Delete"
                                    data-id="{{ $material->id }}" data-name="{{ $material->name }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-wrapper">
            <ul class="pagination" id="pagination"></ul>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/admin/rawmaterials.js') }}?v=1.2"></script>
@endpush