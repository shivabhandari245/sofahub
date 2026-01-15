@extends('layouts.admin')

@section('title', 'Material History - ' . $material->name)

@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/materialhistory.css') }}" />

@endpush
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-gray-800">
                <i class="fas fa-history text-primary"></i> Material History
            </h1>
            <p class="text-muted mb-0">{{ $material->name }} - Transaction Records</p>
        </div>
        <div>
            <a href="{{ url('/admin/rawmaterials') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Materials
            </a>
        </div>
    </div>

    <!-- Material Details Card -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow border-left-primary">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-box"></i> Material Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="35%" class="text-muted">Material Name:</th>
                                    <td class="font-weight-bold h5">{{ $material->name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Category:</th>
                                    <td>
                                        <span class="badge badge-primary badge-pill px-3">
                                            {{ $material->category->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Supplier:</th>
                                    <td>{{ $material->supplier->name ?? 'No Supplier' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Unit:</th>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $material->unit->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Location:</th>
                                    <td>
                                        <i class="fas fa-map-marker-alt text-muted"></i>
                                        {{ $material->storage_location ?? 'Not specified' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="40%" class="text-muted">Current Stock:</th>
                                    <td class="font-weight-bold h4 {{ $material->quantity == 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($material->quantity) }} {{ $material->unit->name ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Unit Cost:</th>
                                    <td class="h5">
                                        <i class="fas fa-rupee-sign text-success"></i> 
                                        {{ number_format($material->unit_cost, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Total Value:</th>
                                    <td class="h5 text-success font-weight-bold">
                                        <i class="fas fa-rupee-sign"></i> 
                                        {{ number_format($material->total_cost, 2) }}
                                    </td>
                                </tr>
                                @if($material->min_stock_level || $material->max_stock_level)
                                <tr>
                                    <th class="text-muted">Stock Range:</th>
                                    <td>
                                        <small class="text-muted">
                                            Min: {{ $material->min_stock_level ?? 'N/A' }} | 
                                            Max: {{ $material->max_stock_level ?? 'N/A' }}
                                        </small>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="col-lg-4">
            <div class="card shadow border-left-success h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-chart-line"></i> History Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-center">
                            <div class="h2 font-weight-bold text-success">{{ $totalRestocked }}</div>
                            <small class="text-muted">Total Restocked</small>
                        </div>
                        <div class="text-center">
                            <div class="h2 font-weight-bold text-warning">{{ $totalUsed }}</div>
                            <small class="text-muted">Total Used</small>
                        </div>
                        <div class="text-center">
                            <div class="h2 font-weight-bold text-primary">{{ $totalTransactions }}</div>
                            <small class="text-muted">Transactions</small>
                        </div>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="mt-4">
                        <h6 class="font-weight-bold">Stock Status</h6>
                        @if($material->quantity == 0)
                            <div class="alert alert-danger py-2">
                                <i class="fas fa-exclamation-circle"></i> Out of Stock
                            </div>
                        @elseif($material->min_stock_level && $material->quantity <= $material->min_stock_level)
                            <div class="alert alert-warning py-2">
                                <i class="fas fa-exclamation-triangle"></i> Low Stock
                            </div>
                        @elseif($material->max_stock_level && $material->quantity >= $material->max_stock_level)
                            <div class="alert alert-info py-2">
                                <i class="fas fa-info-circle"></i> Overstock
                            </div>
                        @else
                            <div class="alert alert-success py-2">
                                <i class="fas fa-check-circle"></i> Stock Level Normal
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Timeline Card -->
    <div class="card shadow mb-4">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold">
                <i class="fas fa-stream"></i> Transaction History
                <span class="badge badge-light ml-2">{{ $history->count() }} records</span>
            </h5>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-light active" id="filterAll">All</button>
                <button type="button" class="btn btn-sm btn-outline-light" id="filterRestocked">Restocked</button>
                <button type="button" class="btn btn-sm btn-outline-light" id="filterUsed">Used</button>
            </div>
        </div>
        <div class="card-body">
            @if($history->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No transaction history available</h5>
                    <p class="text-muted">Start by adding or modifying stock to see history here</p>
                </div>
            @else
                <div class="history-timeline">
                    @foreach($history as $record)
                    <div class="history-item {{ $record->type }}">
                        <div class="card mb-3 border-left-{{ $record->type == 'restocked' ? 'success' : ($record->type == 'used' ? 'warning' : 'info') }}">
                            <div class="card-body py-3">
                                <div class="row align-items-center">
                                    <div class="col-lg-2 col-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="timeline-icon mr-3">
                                                @if($record->type == 'restocked')
                                                    <i class="fas fa-arrow-up text-success fa-2x"></i>
                                                @elseif($record->type == 'used')
                                                    <i class="fas fa-arrow-down text-warning fa-2x"></i>
                                                @else
                                                    <i class="fas fa-plus text-info fa-2x"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">
                                                    {{ $record->created_at->format('M d, Y') }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ $record->created_at->format('h:i A') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-2 col-md-3">
                                        <div class="d-flex align-items-center">
                                            <div class="mr-3">
                                                <span class="badge badge-{{ $record->type == 'restocked' ? 'success' : ($record->type == 'used' ? 'warning' : 'info') }} badge-pill px-3 py-2">
                                                    {{ ucfirst(str_replace('_', ' ', $record->type)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 col-md-4">
                                        <div class="quantity-change">
                                            <div class="d-flex align-items-center">
                                                <div class="text-muted mr-2">From:</div>
                                                <div class="font-weight-bold">{{ number_format($record->old_quantity) }}</div>
                                                <div class="mx-2">
                                                    @if($record->type == 'restocked')
                                                        <i class="fas fa-arrow-right text-success"></i>
                                                    @elseif($record->type == 'used')
                                                        <i class="fas fa-arrow-right text-warning"></i>
                                                    @else
                                                        <i class="fas fa-arrow-right text-info"></i>
                                                    @endif
                                                </div>
                                                <div class="text-muted mr-2">To:</div>
                                                <div class="font-weight-bold">{{ number_format($record->new_quantity) }}</div>
                                            </div>
                                            <div class="mt-1">
                                                @if($record->type == 'restocked')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-plus"></i> +{{ number_format($record->quantity_change) }}
                                                    </span>
                                                @elseif($record->type == 'used')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-minus"></i> -{{ number_format($record->quantity_change) }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-plus"></i> +{{ number_format($record->quantity_change) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-2 col-md-3">
                                        <div class="cost-info">
                                            <small class="text-muted d-block">Unit Cost</small>
                                            <div class="font-weight-bold">
                                                Rs{{ number_format($record->unit_cost, 2) }}
                                            </div>
                                            @if($record->total_cost_change != 0)
                                            <small class="{{ $record->total_cost_change >= 0 ? 'text-success' : 'text-danger' }}">
                                                @if($record->total_cost_change >= 0)
                                                    <i class="fas fa-arrow-up"></i> +Rs{{ number_format($record->total_cost_change, 2) }}
                                                @else
                                                    <i class="fas fa-arrow-down"></i> -Rs{{ number_format(abs($record->total_cost_change), 2) }}
                                                @endif
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-3 col-md-3">
                                        <div class="notes">
                                            <small class="text-muted d-block">Notes</small>
                                            <div class="text-truncate" style="max-width: 200px;" 
                                                 data-toggle="tooltip" title="{{ $record->notes ?? 'No notes' }}">
                                                {{ $record->notes ?? 'No notes' }}
                                            </div>
                                            @if($record->user)
                                            <small class="text-muted">
                                                By: {{ $record->user->name }}
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="d-flex justify-content-center mt-4">
                    {{ $history->links('pagination::bootstrap-4') }}
                </div>

                <!-- Export Options -->
                <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                Showing {{ $history->count() }} of {{ $history->count() }} records
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" id="printHistory">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-outline-success btn-sm" id="exportCSV">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Restock Material</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span class="text-white">&times;</span>
                </button>
            </div>
            <form id="restockForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="material_id" id="restockMaterialId">
                    <div class="form-group">
                        <label>Quantity to Add *</label>
                        <input type="number" name="restock_quantity" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>New Unit Cost (Rs) *</label>
                        <input type="number" name="unit_cost" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2" 
                                  placeholder="Reason for restock, supplier details, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Confirm Restock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Use Stock Modal -->
<div class="modal fade" id="useStockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Use Material Stock</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span class="text-white">&times;</span>
                </button>
            </div>
            <form id="useStockForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="material_id" id="useMaterialId">
                    <div class="form-group">
                        <label>Current Stock: <span id="currentStock" class="font-weight-bold"></span></label>
                    </div>
                    <div class="form-group">
                        <label>Quantity to Use *</label>
                        <input type="number" name="use_quantity" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Purpose / Notes *</label>
                        <textarea name="notes" class="form-control" rows="3" required
                                  placeholder="What is this material being used for?"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Confirm Usage
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@push('scripts')
<script src="{{ asset('js/admin/historymaterial.js') }}?v=1.2"></script>
@endpush
@endsection