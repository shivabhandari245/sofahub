@extends('layouts.admin')
@section('title', 'Production Management')
@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/production.css') }}" />
@endpush

<div class="production-container">
    <div class="card">
        <div class="card-header">
            <div>
                <h2>Factory Production Overview</h2>
                <p>Manage all manufacturing batches and assign team leaders.</p>
            </div>
            
                <a href="{{ url('admin/viewcompletedbatches') }}" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> View Completed
            </a>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <h3>Total Batches</h3>
            <p id="totalBatches">0</p>
        </div>
        <div class="summary-card">
            <h3>Saved Products</h3>
            <p id="savedProducts">0</p>
        </div>
         <div class="summary-card">
            <h3>Pending</h3>
            <p id="pending">0</p>
        </div>
        <div class="summary-card">
            <h3>Delayed</h3>
            <p id="delayed">0</p>
        </div>
       
    </div>

    <div class="header-actionsflex">
        <button class="btn-add" onclick="openBatchModal()">
            <i class="bi bi-plus-circle"></i> Add New Batch
        </button>
         <input type="text" id="searchInput" placeholder="🔍 Search batches..." />

        <div class="searchcard">
            <div class="search-form">
                <select id="filterStatus">
                    <option value="all">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Delayed">Delayed</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Batch Modal -->
    <div id="batchModal" class="modal-overlay" style="display:none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3><i class="bi bi-plus-circle"></i> <span id="modalTitle">Add New Batch</span></h3>
                <button class="modal-close" onclick="closeModal('batchModal')">&times;</button>
            </div>
            <form id="batchForm">
                @csrf
                <input type="hidden" name="batch_id" id="batch_id">
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="batchproduct_id">Product Name *</label>
                            <div class="select-with-btn">
                                <select name="batchproduct_id" id="batchproduct_id" class="form-control" required>
                                    <option value="">Select Product</option>
                                    @foreach($batchproducts as $batchproduct)
                                    <option value="{{ $batchproduct->id }}">{{ $batchproduct->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn-icon" onclick="openModal('addProductModal')">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            </div>
                        </div>



                        <div class="form-group">
                            <label for="leader_name">Leader Name *</label>
                            <input type="text" name="leader_name" id="leader_name" class="form-control" required
                                placeholder="Enter team leader name">
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity *</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" required min="1"
                                placeholder="Enter quantity">
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="expected_completion">Expected Completion *</label>
                            <input type="date" name="expected_completion" id="expected_completion" class="form-control"
                                required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('batchModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Add Batch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal-overlay" style="display:none;">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3><i class="bi bi-box"></i> Product Management</h3>
                <button class="modal-close" onclick="closeModal('addProductModal')">&times;</button>
            </div>

            <div class="modal-tabs">
                <button class="tab-btn active" onclick="switchTab('addProductTab')">Add New Product</button>
                <button class="tab-btn" onclick="switchTab('manageProductsTab')">Manage Products</button>
            </div>

            <div id="addProductTab" class="tab-content active">
                <form id="addProductForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="product_name">Product Name *</label>
                            <input type="text" name="name" id="product_name" class="form-control" required
                                placeholder="Enter product name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="productcategory_id">Category *</label>
                        <div class="select-with-btn">
                            <select name="productcategory_id" id="productcategory_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($productcategories as $productcategory)
                                <option value="{{ $productcategory->id }}">{{ $productcategory->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-icon" onclick="openModal('addCategoryModal')">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="productquality_id">Quality *</label>
                        <div class="select-with-btn">
                            <select name="productquality_id" id="productquality_id" class="form-control" required>
                                <option value="">Select Quality</option>
                                @foreach($productqualities as $productquality)
                                <option value="{{ $productquality->id }}">{{ $productquality->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-icon" onclick="openModal('addQualityModal')">
                                <i class="bi bi-plus-circle"></i>
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="closeModal('addProductModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>

            <div id="manageProductsTab" class="tab-content">
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="table-container">
                        <table class="datatable-">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Product Name</th>
                                    <th>Product Cost</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <!-- Will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('addProductModal')">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="bi bi-tags"></i> Category Management</h3>
                <button class="modal-close" onclick="closeModal('addCategoryModal')">&times;</button>
            </div>

            <div class="modal-tabs">
                <button class="tab-btn active" onclick="switchTab('addCategoryTab')">Add New Category</button>
                <button class="tab-btn" onclick="switchTab('manageCategoriesTab')">Manage Categories</button>
            </div>

            <div id="addCategoryTab" class="tab-content active">
                <form id="addCategoryForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="category_name">Category Name *</label>
                            <input type="text" name="name" id="category_name" class="form-control" required
                                placeholder="Enter category name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="closeModal('addCategoryModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>

            <div id="manageCategoriesTab" class="tab-content">
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="table-container">
                        <table class="datatable-">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Category Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                <!-- Will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('addCategoryModal')">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Quality Modal -->
    <div id="addQualityModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="bi bi-star"></i> Quality Management</h3>
                <button class="modal-close" onclick="closeModal('addQualityModal')">&times;</button>
            </div>

            <div class="modal-tabs">
                <button class="tab-btn active" onclick="switchTab('addQualityTab')">Add New Quality</button>
                <button class="tab-btn" onclick="switchTab('manageQualitiesTab')">Manage Qualities</button>
            </div>

            <div id="addQualityTab" class="tab-content active">
                <form id="addQualityForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="quality_name">Quality Name *</label>
                            <input type="text" name="name" id="quality_name" class="form-control" required
                                placeholder="Enter quality name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            onclick="closeModal('addQualityModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Quality</button>
                    </div>
                </form>
            </div>

            <div id="manageQualitiesTab" class="tab-content">
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="table-container">
                        <table class="datatable-">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Quality Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="qualitiesTableBody">
                                <!-- Will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('addQualityModal')">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Records Table -->
    <div class="card">
        <div class="card-header">
            <h3>Batch Records</h3>
        </div>
        <div class="table-container">
            <table id="productionTable" class="data-table">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Quality</th>
                        <th>Leader</th>
                        <th>Quantity</th>
                        <th>Expected Unit Cost (NPR)</th>
                        <th>Total Cost (NPR)</th>
                        <th>Start Date</th>
                        <th>Expected Completion</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="batchBody">
                    <!-- Will be loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Insufficient Materials Modal -->
    <div class="modal fade" id="insufficientModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Insufficient Raw Materials</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p class="fw-bold text-danger">You cannot create this batch because the following materials are
                        insufficient:</p>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Required</th>
                                <th>Available</th>
                                <th>Shortage</th>
                            </tr>
                        </thead>
                        <tbody id="insufficientTable"></tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>




    {{-- Cost Modal --}}
    <div id="costModal" class="modal-overlay" style="display:none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3><i class="bi bi-calculator"></i> Calculate Production Cost</h3>
                <button class="modal-close" onclick="closeModal('costModal')">&times;</button>
            </div>
            <form id="costForm">
                @csrf
                <input type="hidden" id="cost_batch_id" name="batch_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cost_labor_cost">Labor Cost (NPR) *</label>
                        <input type="number" id="cost_labor_cost" name="labor_cost" class="form-control" required
                            step="0.01" min="0" placeholder="Enter labor cost">
                    </div>
                    <div class="form-group">
                        <label for="cost_other_expenses">Other Expenses (NPR) *</label>
                        <input type="number" id="cost_other_expenses" name="other_expenses" class="form-control"
                            required step="0.01" min="0" placeholder="Enter other expenses">
                    </div>
                    <div class="alert alert-info">
                        <small><i class="bi bi-info-circle"></i> Material cost will be calculated from product
                            recipe.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('costModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Save & Complete Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')

<script src="{{ asset('js/admin/production.js') }}"></script>
@endpush