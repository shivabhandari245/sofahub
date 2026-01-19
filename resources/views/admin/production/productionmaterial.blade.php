@extends('layouts.admin')
@section('title', 'Allocate Raw Materials')
@section('content')
<link rel="stylesheet" href="{{ asset('css/admincss/productionmaterials.css') }}" />

<div class="production-materials-container">
    <div class="page-header">
        <h1 class="page-title">Production Material Allocation</h1>
        <div class="batch-info">
            <span class="batch-badge">Product Name: {{ $batchproduct->name }}</span>
            <span class="quantity-badge">{{ $batchproduct->quantity }} units</span>
        </div>
    </div>

    <!-- Status Messages -->
    <div id="messageContainer"></div>

    <div class="materials-grid">
        <!-- Left Column: Available Materials -->
        <section class="materials-section" aria-labelledby="available-materials-heading">
            <div class="section-card">
                <div class="section-card-header">
                    <h2 id="available-materials-heading" class="section-title">
                        <i class="fas fa-box-seam"></i>
                        Select Raw Material
                    </h2>
                </div>

                <div class="section-card-body">
                    <!-- Category Filter and Search -->
                    <div class="row">
                        <div class="col-md-6">
                            <label>Material Category</label>
                            <select id="material_category" class="form-control">
                                <option value="">-- Select Category --</option>
                                @foreach ($materialTypes as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Search Raw Materials</label>
                            <input type="text" id="searchMaterial" class="form-control"
                                placeholder="Search by name, supplier, etc." disabled>
                        </div>
                    </div>

                    <!-- Available Materials Table -->
                    <div class="table-section">
                        <div class="table-header">
                            <h3 class="table-title">Available Materials</h3>
                            <span class="table-subtitle">Click on a material to select</span>
                        </div>
                        <div class="table-container">
                            <table id="availableMaterialsTable" class="materials-table">
                                <thead class="table-head">
                                    <tr>
                                        <th scope="col">Material Name</th>
                                        <th scope="col">Supplier</th>
                                        <th scope="col">Available Stock</th>
                                        <th scope="col">Unit</th>
                                        <th scope="col">Unit Cost</th>
                                        <th scope="col">Storage Location</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="table-body">
                                    <tr>
                                        <td colspan="7" class="table-empty-state">
                                            <i class="fas fa-inbox"></i>
                                            Select a category to view materials
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right Column: Selected Materials & Production Summary -->
        <section class="selection-section" aria-labelledby="selected-materials-heading">
            <!-- Selected Materials Card -->
            <div class="section-card">
                <div class="section-card-header">
                    <h2 id="selected-materials-heading" class="section-title">
                        <i class="fas fa-clipboard-check"></i>
                        Allocated Materials
                    </h2>
                </div>

                <div class="selection-content">
                    <!-- Selected Materials List -->
                    <div class="selected-materials-column">
                        <div class="selected-header">
                            <h3 class="selected-title">Allocated Materials</h3>
                            <span class="selected-count" id="selectedCount">{{ $allocatedMaterials->count() }}
                                items</span>
                        </div>

                        <div class="table-container">
                            <table class="materials-table">
                                <thead class="table-head">
                                    <tr>
                                        <th scope="col">Material</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Qty Used</th>
                                        <th scope="col">Unit Cost</th>
                                        <th scope="col">Total Cost</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="allocatedTable">
                                    @if($allocatedMaterials->isEmpty())
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                                                <p class="text-muted">No materials allocated yet</p>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($allocatedMaterials as $material)
                                        <tr id="material-{{ $material->id }}" class="fade-in">
                                            <td>{{ $material->rawMaterial->name ?? 'Unknown' }}</td>
                                            <td>{{ $material->rawMaterial->category->name ?? 'Unknown' }}</td>
                                            <td>{{ $material->quantity_used }}</td>
                                            <td>Rs {{ number_format($material->unit_cost, 2) }}</td>
                                            <td>Rs {{ number_format($material->total_cost, 2) }}</td>
                                            <td>
                                                <button class="btn btn-danger btn-sm deleteBtn" 
                                                        data-id="{{ $material->id }}"
                                                        data-material="{{ $material->rawMaterial->name ?? 'Unknown' }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cost Summary Card -->
            <div class="section-card">
                <div class="section-card-header">
                    <h3 class="section-title">
                        <i class="fas fa-calculator"></i>
                        Cost Summary
                    </h3>
                </div>
                <div class="section-card-body">
                    <div class="summary-grid">
                        <div class="summary-itemtotal-cost">
                            <div class="summary-label">
                                <span>Total Materials Cost</span>
                            </div>
                            <div id="totalCost" class="summary-value">
                                Rs
                                {{ number_format($allocatedMaterials->sum(function($item) { return $item->unit_cost * $item->quantity_used; }), 2) }}
                            </div>

                                                <!-- Confirm Form -->
                    <form id="confirmForm" method="POST"
                        action="{{ url('admin/confirmBatchProduct/'.$batchproduct->id) }}">
                        @csrf

                        <div class="form-actions">
 
                            <a href="{{ url('admin/production') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Batches
                            </a>
                        </div>
                    </form>
                        </div>
                    </div>


                    @if (session('success'))
                    <div class="alert alert-success mt-3">{{ session('success') }}</div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Quantity Modal -->
<div class="modal fade" id="quantityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-cube"></i> Allocate Material
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 id="modalMaterialName" class="text-primary"></h6>
                    <small class="text-muted" id="modalMaterialDetails"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Available Stock</label>
                    <div class="form-control bg-light" id="availableStockDisplay"></div>
                </div>

                <div class="mb-3">
                    <label for="allocateQuantity" class="form-label">Quantity to Allocate *</label>
                    <input type="number" class="form-control" id="allocateQuantity" min="1" step="0.01"
                        placeholder="Enter quantity">
                    <div class="form-text">Enter the quantity you want to use for production</div>
                </div>

                <input type="hidden" id="selectedMaterialId">
                <input type="hidden" id="selectedMaterialCost">
                <input type="hidden" id="selectedMaterialCategory">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAllocation">
                    <i class="fas fa-check"></i> Confirm Allocation
                </button>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
window.batchproductId = "{{ $batchproduct->id }}";
</script>
<script src="{{ asset('js/admin/productionmaterial.js') }}"></script>
@endpush