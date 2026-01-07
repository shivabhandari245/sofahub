@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/dashboard.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
:root {
    /* Color Palette */
    --primary-color: #4361ee;
    --primary-light: rgba(67, 97, 238, 0.1);
    --primary-dark: #3a56d4;
    --secondary-color: #7209b7;
    --success-color: #4cc9f0;
    --warning-color: #f72585;
    --danger-color: #f44336;
    --info-color: #4CAF50;

    /* Text Colors */
    --text-primary: #2d3748;
    --text-secondary: #718096;
    --text-muted: #a0aec0;

    /* Background Colors */
    --bg-light: #f8fafc;
    --bg-card: #ffffff;
    --border-color: #e2e8f0;

    /* Shadows */
    --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 50px -12px rgba(0, 0, 0, 0.25);

    /* Border Radius */
    --radius-sm: 6px;
    --radius-md: 12px;
    --radius-lg: 16px;

    /* Transitions */
    --transition-fast: 150ms ease;
    --transition-base: 250ms ease;
    --transition-slow: 350ms ease;

    /* Spacing */
    --spacing-xs: 0.5rem;
    --spacing-sm: 1rem;
    --spacing-md: 1.5rem;
    --spacing-lg: 2rem;
    --spacing-xl: 2.5rem;
}

/* ==========================================================================
   Base Container
   ========================================================================== */
.dashboard-container {
    padding: var(--spacing-lg);
    background: var(--bg-light);
    min-height: 100vh;
}

/* ==========================================================================
   Typography
   ========================================================================== */
.dashboard-container h2 {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.2;
    margin-bottom: 0.75rem;
}

.dashboard-container h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.dashboard-container h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-secondary);
    line-height: 1.2;
    margin-bottom: 0.25rem;
}

.dashboard-container p {
    color: var(--text-secondary);
    line-height: 1.5;
    margin-bottom: 1rem;
}

/* ==========================================================================
   Welcome Card
   ========================================================================== */
.welcome-card {
    background: linear-gradient(135deg, black 0%, var(--secondary-color) 100%);
    color: white;
    padding: var(--spacing-xl);
    border-radius: var(--radius-lg);
    margin-bottom: var(--spacing-xl);
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

.welcome-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    pointer-events: none;
}

.welcome-card h2 {
    color: white;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: var(--spacing-sm);
    position: relative;
    z-index: 1;
}

.welcome-card p {
    opacity: 0.9;
    font-size: 1.1rem;
    margin: 0;
    position: relative;
    z-index: 1;
    max-width: 600px;
}

/* ==========================================================================
   Quick Links Section
   ========================================================================== */
.quick-links-section {
    margin-bottom: var(--spacing-xl);
}

.quick-links-section h3 {
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 2px solid var(--border-color);
}

.quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.quick-link-card {
    background: var(--bg-card);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    text-align: center;
    transition: all var(--transition-base);
    border: 2px solid transparent;
    text-decoration: none;
    color: var(--text-primary);
    display: block;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.quick-link-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    transform: translateY(-100%);
    transition: transform var(--transition-base);
}

.quick-link-card:hover::before {
    transform: translateY(0);
}

.quick-link-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-light);
}

.quick-link-card i {
    font-size: 2rem;
    margin-bottom: var(--spacing-sm);
    color: var(--primary-color);
    display: inline-block;
    transition: transform var(--transition-base);
}

.quick-link-card:hover i {
    transform: scale(1.1);
}

.quick-link-card h4 {
    margin-bottom: var(--spacing-xs);
    font-size: 1.125rem;
}

.quick-link-card small {
    color: var(--text-muted);
    font-size: 0.875rem;
    display: block;
}

/* ==========================================================================
   Stats Grid
   ========================================================================== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-xl);
}

.stat-card {
    background: var(--bg-card);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    border-left: 4px solid var(--primary-color);
    transition: all var(--transition-base);
    position: relative;
    overflow: hidden;
}

.stat-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 0%, rgba(255, 255, 255, 0.4) 100%);
    opacity: 0;
    transition: opacity var(--transition-base);
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
}

.stat-card:hover::after {
    opacity: 1;
}

/* Stat Card Color Variations */
.stat-card.orange {
    border-left-color: #ff9800;
}

.stat-card.green {
    border-left-color: var(--info-color);
}

.stat-card.blue {
    border-left-color: #2196F3;
}

.stat-card.purple {
    border-left-color: var(--secondary-color);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-md);
    position: relative;
    z-index: 1;
}

.stat-header h3 {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin: 0;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

/* Stat Icon Background Colors */
.stat-card .stat-icon {
    background: var(--primary-light);
    color: var(--primary-color);
}

.stat-card.orange .stat-icon {
    background: rgba(255, 152, 0, 0.1);
    color: #ff9800;
}

.stat-card.green .stat-icon {
    background: rgba(76, 175, 80, 0.1);
    color: var(--info-color);
}

.stat-card.blue .stat-icon {
    background: rgba(33, 150, 243, 0.1);
    color: #2196F3;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--spacing-xs);
    position: relative;
    z-index: 1;
    font-family: 'Inter', 'Segoe UI', sans-serif;
}

.stat-change {
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    position: relative;
    z-index: 1;
}

.stat-change.positive {
    color: var(--info-color);
}

.stat-change.negative {
    color: var(--danger-color);
}

/* ==========================================================================
   Charts Section
   ========================================================================== */
.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-xl);
}

.chart-container {
    background: var(--bg-card);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    transition: box-shadow var(--transition-base);
}

.chart-container:hover {
    box-shadow: var(--shadow-lg);
}

.chart-container h3 {
    margin-bottom: var(--spacing-md);
    font-size: 1.125rem;
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.chart-wrapper {
    position: relative;
    height: 280px;
    width: 100%;
}

/* ==========================================================================
   Tables Section
   ========================================================================== */
.tables-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: var(--spacing-md);
}

.table-container {
    background: var(--bg-card);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    transition: box-shadow var(--transition-base);
}

.table-container:hover {
    box-shadow: var(--shadow-lg);
}

.table-container h3 {
    margin-bottom: var(--spacing-md);
    font-size: 1.125rem;
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding-bottom: var(--spacing-sm);
    border-bottom: 2px solid var(--border-color);
}

.table-container h3 i {
    color: var(--primary-color);
    font-size: 1.25rem;
}

.table-responsive {
    overflow-x: auto;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border-color);
}

.small-table {
    width: 100%;
    font-size: 0.875rem;
    border-collapse: collapse;
    min-width: 500px;
}

.small-table th {
    background: var(--bg-light);
    padding: var(--spacing-sm) 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    border-bottom: 2px solid var(--border-color);
    text-align: left;
    white-space: nowrap;
}

.small-table td {
    padding: var(--spacing-sm) 1.25rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
    color: var(--text-secondary);
}

.small-table tr:last-child td {
    border-bottom: none;
}

.small-table tr:hover {
    background: var(--bg-light);
}

.small-table tbody tr {
    transition: background-color var(--transition-fast);
}

/* ==========================================================================
   Badges & Status Indicators
   ========================================================================== */
.badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    display: inline-block;
}

.badge-success {
    background: rgba(76, 175, 80, 0.1);
    color: var(--info-color);
    border: 1px solid rgba(76, 175, 80, 0.2);
}

.badge-warning {
    background: rgba(255, 152, 0, 0.1);
    color: #ff9800;
    border: 1px solid rgba(255, 152, 0, 0.2);
}

.badge-danger {
    background: rgba(244, 67, 54, 0.1);
    color: var(--danger-color);
    border: 1px solid rgba(244, 67, 54, 0.2);
}

.stock-warning {
    color: var(--danger-color);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.stock-warning::before {
    content: '⚠';
    font-size: 1rem;
}

.stock-ok {
    color: var(--info-color);
    font-weight: 600;
}

/* ==========================================================================
   Empty States
   ========================================================================== */
.text-center {
    text-align: center;
}

.small-table td.text-center {
    color: var(--text-muted);
    font-style: italic;
    padding: 2rem 1.25rem;
}

/* ==========================================================================
   Utility Classes
   ========================================================================== */
.mb-0 {
    margin-bottom: 0 !important;
}

.mt-1 {
    margin-top: var(--spacing-xs);
}

.mt-2 {
    margin-top: var(--spacing-sm);
}

.mt-3 {
    margin-top: var(--spacing-md);
}

/* ==========================================================================
   Responsive Design
   ========================================================================== */

/* Large Screens */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Tablets */
@media (max-width: 768px) {
    .dashboard-container {
        padding: var(--spacing-md);
    }

    .welcome-card {
        padding: var(--spacing-md);
    }

    .welcome-card h2 {
        font-size: 1.5rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: var(--spacing-sm);
    }

    .quick-links {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-sm);
    }

    .charts-section,
    .tables-section {
        grid-template-columns: 1fr;
    }

    .quick-link-card {
        padding: var(--spacing-sm);
    }
}

/* Mobile */
@media (max-width: 480px) {
    .dashboard-container {
        padding: var(--spacing-sm);
    }

    .quick-links {
        grid-template-columns: 1fr;
    }

    .welcome-card {
        padding: var(--spacing-sm);
    }

    .stat-card,
    .chart-container,
    .table-container {
        padding: var(--spacing-sm);
    }
}

/* Print */
@media print {
    .dashboard-container {
        padding: 0;
        background: white;
    }

    .quick-link-card,
    .stat-card:hover,
    .chart-container:hover,
    .table-container:hover {
        box-shadow: none;
        transform: none;
    }

    .welcome-card {
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        border: 1px solid #000;
    }

    .welcome-card h2 {
        color: black;
    }
}
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Welcome Card -->
    <div class="welcome-card">
        <h2>Welcome to SofaHub Admin Panel</h2>
        <p>Monitor and manage all aspects of Factory and Showroom operations from one central dashboard.</p>
    </div>

    <!-- Quick Links -->
    <div class="quick-links-section">
        <h3>Quick Access</h3>
        <div class="quick-links">
            <a href="{{ url('admin/rawmaterials') }}" class="quick-link-card">
                <i class="fas fa-plus-circle"></i>
                <h4>Add New Material</h4>
                <small>Create Material Entry</small>
            </a>
            <a href="{{ url('admin/products') }}" class="quick-link-card">
                <i class="fas fa-boxes"></i>
                <h4>Products</h4>
                <small>Available Products</small>
            </a>
            <a href="{{ url('admin/invoices') }}" class="quick-link-card">
                <i class="fas fa-chart-line"></i>
                <h4>Invoices Report</h4>
                <small>View Invoices</small>
            </a>

            <a href="{{ url('admin/production') }}" class="quick-link-card">
                <i class="fas fa-boxes"></i>
                <h4>Production Batch</h4>
                <small>Batch Management</small>
            </a>

            <a href="{{ url('admin/dispatch') }}" class="quick-link-card">
                <i class="fas fa-shipping-fast"></i>
                <h4>Dispatches</h4>
                <small>Dispatch Management</small>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-header">
                <h3>TOTAL SALES</h3>
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <div class="stat-value counter" data-target="{{ $currentMonthSales }}">0</div>
            <div class="stat-change {{ $salesChange >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $salesChange >= 0 ? 'up' : 'down' }}"></i>
                {{ number_format(abs($salesChange), 1) }}% vs last month
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-header">
                <h3>TOTAL PROFIT</h3>
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="stat-value counter" data-target="{{ $currentMonthProfit }}">0</div>
            <div class="stat-change {{ $profitChange >= 0 ? 'positive' : 'negative' }}">
                <i class="fas fa-arrow-{{ $profitChange >= 0 ? 'up' : 'down' }}"></i>
                {{ number_format(abs($profitChange), 1) }}% vs last month
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-header">
                <h3>UNITS PRODUCED</h3>
                <div class="stat-icon">
                    <i class="fas fa-industry"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($unitsProduced) }}</div>
            <div class="stat-change">
                This Month
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-header">
                <h3>Total Customers</h3>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-value">{{ number_format($customers) }}</div>
            <div class="stat-change">
                Total Customers
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-container">
            <h3>Sales Trend (Last 6 Months)</h3>
            <div class="chart-wrapper">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="chart-container">
            <h3>Production Trend</h3>
            <div class="chart-wrapper">
                <canvas id="productionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="tables-section">
        <!-- Recent Sales -->
        <div class="table-container">
            <h3><i class="fas fa-receipt"></i> Recent Sales</h3>
            <div class="table-responsive">
                <table class="small-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                            <td>${{ number_format($sale->total_amount, 2) }}</td>
                            <td>{{ $sale->created_at->format('M d') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No recent sales</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCounters();
    initializeCharts();
});


function initializeCounters() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200;

    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText.replace(/,/g, '');
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

/**
 * Initialize Charts
 */
function initializeCharts() {
    initializeSalesChart();
    initializeProductionChart();
}

/**
 * Initialize Sales Chart
 */
function initializeSalesChart() {
    const salesCtx = document.getElementById('salesChart').getContext('2d');



    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesMonths,
            datasets: [{
                label: 'Sales ($)',
                data: salesTotals,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize Production Chart
 */
function initializeProductionChart() {
    const productionCtx = document.getElementById('productionChart').getContext('2d');

    // Prepare production data - FIXED SYNTAX (no spaces around ->)


    const productionChart = new Chart(productionCtx, {
        type: 'bar',
        data: {
            labels: productionMonths,
            datasets: [{
                label: 'Units Produced',
                data: productionUnits,
                backgroundColor: '#4CAF50',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + ' units';
                        }
                    }
                }
            }
        }
    });
}
</script>
@endpush