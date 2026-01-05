@extends('layouts.user')

@push('styles')

<link rel="stylesheet" href="{{ asset('css/usercss/dashboard.css') }}" />
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Header -->
    <div class="header-card">
        <h2><i class="fas fa-chart-line"></i> Performance Dashboard</h2>
        <p>Real-time insights into your showroom's performance and growth metrics</p>
        <div class="mt-3">
            <span class="stats-badge badge-success">
                <i class="fas fa-calendar-alt"></i>
                Last Updated: {{ now()->format('M d, Y h:i A') }}
            </span>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="kpi-title">Total Revenue</div>
            <div class="kpi-value">NPR {{ number_format($totalSales, 2) }}</div>
            <div class="kpi-subtitle">All-time sales amount</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="kpi-title">Total Invoices</div>
            <div class="kpi-value">{{ number_format($totalInvoices) }}</div>
            <div class="kpi-subtitle">Processed transactions</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="kpi-title">Top Product</div>
            <div class="kpi-value" id="topProductName">{{ $topProduct->product->name ?? 'N/A' }}</div>
            <div class="kpi-subtitle">
                <span id="topProductQty">{{ $topProduct->total_qty ?? 0 }}</span> units sold
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="kpi-title">Monthly Growth</div>
            <div class="kpi-value {{ $growth >= 0 ? 'text-success' : 'text-danger' }}" id="growthValue">
                {{ $growth }}%
            </div>
            <div class="kpi-subtitle">
                <span class="{{ $growth >= 0 ? 'text-success' : 'text-danger' }}" id="growthText">
                    <i class="fas fa-arrow-{{ $growth >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($growth) }}% {{ $growth >= 0 ? 'increase' : 'decrease' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="section-title">
        <i class="fas fa-chart-bar"></i>
        Performance Analytics
    </div>

    <div class="chart-grid">
        <!-- Sales Trend Chart -->
        <div class="chart-box">
            <div class="chart-header">
                <h3><i class="fas fa-trend-up text-primary"></i> Monthly Sales Trend</h3>
                <span class="stats-badge badge-success" id="currentMonthSales">
                    <i class="fas fa-arrow-up"></i>
                    Current Month: NPR {{ number_format($monthlySales[date('n')] ?? 0, 2) }}
                </span>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: linear-gradient(135deg, #667eea, #764ba2);"></div>
                    <span>Monthly Revenue</span>
                </div>
            </div>
        </div>

        <!-- Stock Status Chart -->
        <div class="chart-box">
            <div class="chart-header">
                <h3><i class="fas fa-boxes text-warning"></i> Inventory Status</h3>
                <span class="stats-badge {{ $productStatus['Low Stock'] > 0 ? 'badge-danger' : 'badge-success' }}" id="lowStockBadge">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $productStatus['Low Stock'] }} Low Stock Items
                </span>
            </div>
            <div class="chart-container">
                <canvas id="productChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #10b981;"></div>
                    <span>Available (<span id="availableCount">{{ $productStatus['Available'] }}</span>)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #f59e0b;"></div>
                    <span>Low Stock (<span id="lowStockCount">{{ $productStatus['Low Stock'] }}</span>)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ef4444;"></div>
                    <span>Out of Stock (<span id="outOfStockCount">{{ $productStatus['Out of Stock'] }}</span>)</span>
                </div>
            </div>
        </div>

        <!-- Sales Status Chart -->
        <div class="chart-box">
            <div class="chart-header">
                <h3><i class="fas fa-check-circle text-success"></i> Transaction Status</h3>
                <span class="stats-badge badge-success" id="successRateBadge">
                    <i class="fas fa-percentage"></i>
                    {{ $totalInvoices > 0 ? round(($saleStatus['Completed'] / $totalInvoices) * 100, 1) : 0 }}% Success Rate
                </span>
            </div>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #10b981;"></div>
                    <span>Completed (<span id="completedCount">{{ $saleStatus['Completed'] }}</span>)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ef4444;"></div>
                    <span>Returned (<span id="returnedCount">{{ $saleStatus['Returned'] }}</span>)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="section-title">
        <i class="fas fa-info-circle"></i>
        Quick Stats
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Avg. Order Value</div>
                <div class="stat-value" id="avgOrderValue">
                    NPR {{ $totalInvoices > 0 ? number_format($totalSales / $totalInvoices, 2) : '0.00' }}
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">This Month Revenue</div>
                <div class="stat-value text-success" id="thisMonthRevenue">
                    NPR {{ number_format($monthlySales[date('n')] ?? 0, 2) }}
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Inventory Items</div>
                <div class="stat-value" id="totalItems">
                    {{ array_sum($productStatus) }}
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-title">Conversion Rate</div>
                <div class="stat-value text-primary" id="conversionRate">
                    {{ $totalInvoices > 0 ? round(($saleStatus['Completed'] / $totalInvoices) * 100, 1) : 0 }}%
                </div>
            </div>
        </div>
    </div>
</div>


<script type="application/json" id="dashboardData">
{
    "monthlySales": @json($monthlySales),
    "productStatus": @json($productStatus),
    "saleStatus": @json($saleStatus),
    "totalInvoices": {{ $totalInvoices }},
    "totalSales": {{ $totalSales }},
    "growth": {{ $growth }}
}
</script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script src="{{ asset('js/user/dashboard.js') }}"></script>
@endpush