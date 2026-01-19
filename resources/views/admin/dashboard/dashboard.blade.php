@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/dashboard.css') }}">

@endpush

@section('content')
<div class="dashboard-container">
    <div class="dashboard-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-card">
                <div class="welcome-content">
                    <h1>Welcome Back, Admin! 👋</h1>
                    <p>Monitor and manage factory operations, track inventory, and optimize production workflow from your central dashboard.</p>
                </div>
                <div class="welcome-stats">
                    <div class="stat-item">
                        <span class="stat-number">{{ $totalProducts }}</span>
                        <span class="stat-label">Products</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">{{ $customers }}</span>
                        <span class="stat-label">Customers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">{{ $unitsProduced }}</span>
                        <span class="stat-label">Produced</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 class="section-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h2>
            <div class="action-grid">
                <a href="{{ url('admin/rawmaterials') }}" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="action-content">
                        <h3>Add New Material</h3>
                        <p>Create new raw material entry with pricing and stock levels</p>
                    </div>
                </a>
                
                <a href="{{ url('admin/production') }}" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-industry"></i>
                    </div>
                    <div class="action-content">
                        <h3>Production Batch</h3>
                        <p>Create and manage production batches</p>
                    </div>
                </a>
                
                <a href="{{ url('admin/dispatch') }}" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="action-content">
                        <h3>Dispatch Management</h3>
                        <p>Track and manage product dispatches</p>
                    </div>
                </a>
                
                <a href="{{ url('admin/invoices') }}" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="action-content">
                        <h3>Sales Reports</h3>
                        <p>View and analyze sales performance</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="key-metrics">
            <h2 class="section-title">
                <i class="fas fa-chart-line"></i>
                Performance Metrics
            </h2>
            <div class="metrics-grid">
                <div class="metric-card sales">
                    <div class="metric-header">
                        <h3>Monthly Sales</h3>
                        <div class="metric-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="metric-value counter" data-target="{{ $currentMonthSales }}">0</div>
                    <div class="metric-change {{ $salesChange >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ $salesChange >= 0 ? 'up' : 'down' }}"></i>
                        {{ number_format(abs($salesChange), 1) }}% from last month
                    </div>
                </div>
                
                <div class="metric-card profit">
                    <div class="metric-header">
                        <h3>Monthly Profit</h3>
                        <div class="metric-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="metric-value counter" data-target="{{ $currentMonthProfit }}">0</div>
                    <div class="metric-change {{ $profitChange >= 0 ? 'positive' : 'negative' }}">
                        <i class="fas fa-arrow-{{ $profitChange >= 0 ? 'up' : 'down' }}"></i>
                        {{ number_format(abs($profitChange), 1) }}% from last month
                    </div>
                </div>
                
                <div class="metric-card production">
                    <div class="metric-header">
                        <h3>Units Produced</h3>
                        <div class="metric-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                    </div>
                    <div class="metric-value">{{ number_format($unitsProduced) }}</div>
                    <div class="metric-change positive">
                        <i class="fas fa-check-circle"></i>
                        This month
                    </div>
                </div>
                
                <div class="metric-card customers">
                    <div class="metric-header">
                        <h3>Total Customers</h3>
                        <div class="metric-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="metric-value">{{ number_format($customers) }}</div>
                    <div class="metric-change positive">
                        <i class="fas fa-user-plus"></i>
                        Registered customers
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Alerts Section -->
        <div class="stock-alerts">
            <h2 class="section-title">
                <i class="fas fa-exclamation-triangle"></i>
                Stock Alerts
            </h2>
            <div class="alerts-container">
                <div class="alert-tabs">
                    <button class="alert-tab active" onclick="showAlertTab('raw-materials')">
                        <i class="fas fa-cubes"></i>
                        Raw Materials
                    </button>
                    <button class="alert-tab" onclick="showAlertTab('products')">
                        <i class="fas fa-box"></i>
                        Products
                    </button>
                </div>
                
                <div class="alert-content">
                    <!-- Raw Materials Tab -->
                    <div id="raw-materials-tab" class="alert-tab-content">
                        <table class="alert-table">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Current Stock</th>
                                    <th>Min Level</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $lowRawMaterials = \App\Models\RawMaterialModel::where('quantity', '<', 10)->get();
                                @endphp
                                
                                @forelse($lowRawMaterials as $material)
                                <tr>
                                    <td>
                                        <strong>{{ $material->name }}</strong>
                                        @if($material->category)
                                        <br><small class="text-muted">{{ $material->category->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $material->quantity }} {{ $material->unit->name ?? '' }}</td>
                                    <td>{{ $material->min_stock_level ?? 10 }}</td>
                                    <td>
                                        @if($material->quantity == 0)
                                        <span class="stock-badge critical">Out of Stock</span>
                                        @elseif($material->quantity < 5)
                                        <span class="stock-badge critical">Critical</span>
                                        @else
                                        <span class="stock-badge low">Low Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('admin/rawmaterials/') }}" class="action-btn">
                                            <i class="fas fa-sync-alt"></i> Restock
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                        <p class="text-muted">All raw materials are sufficiently stocked</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Products Tab -->
                    <div id="products-tab" class="alert-tab-content" style="display: none;">
                        <table class="alert-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $lowProducts = \App\Models\ProductModel::where('quantity', '<=', 5)->get();
                                @endphp
                                
                                @forelse($lowProducts as $product)
                                <tr>
                                    <td>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->quality)
                                        <br><small class="text-muted">{{ $product->quality ?? '-' }}

                                        </small>
                                        @endif
                                    </td>
                                    <td>{{ $product->category ?? '-' }}</td>
                                    <td>{{ $product->quantity }}</td>
                                    <td>
                                        @if($product->quantity == 0)
                                        <span class="stock-badge critical">Out of Stock</span>
                                        @elseif($product->quantity < 3)
                                        <span class="stock-badge critical">Critical</span>
                                        @else
                                        <span class="stock-badge low">Low Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('admin/production') }}" class="action-btn">
                                            <i class="fas fa-plus"></i> Produce
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                        <p class="text-muted">All products are sufficiently stocked</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        <!-- Recent Activity -->
        <div class="recent-activity">
            <div class="activity-card">
                <h2 class="section-title">
                    <i class="fas fa-history"></i>
                    Recent Sales
                </h2>
                <div class="activity-list">
                    @forelse($recentSales as $sale)
                    <div class="activity-item sale">
                        <div class="activity-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Order #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</h4>
                            <p>{{ $sale->customer->name ?? 'Walk-in Customer' }}</p>
                            <p class="text-success">${{ number_format($sale->total_amount, 2) }}</p>
                            <div class="activity-time">{{ $sale->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-chart-line text-muted fa-2x mb-2"></i>
                        <p class="text-muted">No recent sales</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <div class="activity-card">
                <h2 class="section-title">
                    <i class="fas fa-industry"></i>
                    Recent Production
                </h2>
                <div class="activity-list">
                    @php
                    $recentBatches = \App\Models\BatchModel::with('product')
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                    @endphp
                    
                    @forelse($recentBatches as $batch)
                    <div class="activity-item production">
                        <div class="activity-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="activity-content">
                            <h4>{{ $batch->product->name ?? 'Unknown Product' }}</h4>
                            <p>Leader: {{ $batch->leader_name }}</p>
                            <p class="text-info">{{ $batch->quantity }} units produced</p>
                            <div class="activity-time">{{ $batch->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-industry text-muted fa-2x mb-2"></i>
                        <p class="text-muted">No recent production</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCounters();
    initializeTabSwitcher();
});

// Initialize counter animations
function initializeCounters() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200;

    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText.replace(/[^0-9.-]+/g, "");
            const increment = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment).toLocaleString();
                setTimeout(updateCount, 20);
            } else {
                counter.innerText = target.toLocaleString();
            }
        };
        updateCount();
    });
}

// Tab switcher for stock alerts
function initializeTabSwitcher() {
    function showAlertTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.alert-tab-content').forEach(tab => {
            tab.style.display = 'none';
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.alert-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Show selected tab
        document.getElementById(`${tabName}-tab`).style.display = 'block';
        
        // Add active class to clicked tab
        event.target.classList.add('active');
    }
    
    // Make function global
    window.showAlertTab = showAlertTab;
}



// Refresh stock alerts periodically (every 30 seconds)
setInterval(function() {
    fetch('{{ route("admin.dashboard.stock-alerts") }}')
        .then(response => response.json())
        .then(data => {
            // Update raw materials table
            if (data.raw_materials && data.raw_materials.length > 0) {
                // Update table logic here
            }
            
            // Update products table
            if (data.products && data.products.length > 0) {
                // Update table logic here
            }
        });
}, 30000);

// Export dashboard data
function exportDashboardData() {
    const exportData = {
        sales: {!! $currentMonthSales !!},
        profit: {!! $currentMonthProfit !!},
        production: {!! $unitsProduced !!},
        customers: {!! $customers !!},
        low_raw_materials: {!! $lowRawMaterials->count() !!},
        low_products: {!! $lowProducts->count() !!}
    };
    
    const dataStr = JSON.stringify(exportData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = 'dashboard-export-' + new Date().toISOString().split('T')[0] + '.json';
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
}
</script>
@endpush