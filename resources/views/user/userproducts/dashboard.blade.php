@extends('layouts.user')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/usercss/dashboard.css') }}" />
<style>
/* === Dashboard Container === */
.dashboard-container {
    padding: 20px;
    font-family: 'Inter', sans-serif;
}

/* Header Card */
.header-card {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.header-card h2 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 10px;
}

.stats-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 14px;
}

.badge-success { background: #10b981; color: #fff; }
.badge-danger { background: #ef4444; color: #fff; }

/* === Quick Actions === */
.section-title {
    font-size: 18px;
    font-weight: 600;
    margin: 30px 0 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.quick-action-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    font-weight: 500;
    font-size: 14px;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.quick-action-card i {
    font-size: 24px;
    margin-bottom: 8px;
    color: #4f46e5;
}

.quick-action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.quick-action-card a {
    display: block;
    margin-top: 5px;
    text-decoration: none;
    color: #111827;
}

/* === KPI Cards === */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.kpi-card {
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.kpi-icon {
    font-size: 28px;
    color: #4f46e5;
}

.kpi-title {
    font-weight: 600;
    color: #374151;
}

.kpi-value {
    font-size: 22px;
    font-weight: 700;
    color: #111827;
}

.kpi-subtitle {
    font-size: 12px;
    color: #6b7280;
}

.text-success { color: #10b981; }
.text-danger { color: #ef4444; }
.text-primary { color: #4f46e5; }

/* === Charts Section === */
.chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.chart-box {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.05);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.chart-container {
    height: 250px;
}

/* === Quick Stats === */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.05);
}

.stat-icon i {
    font-size: 28px;
    color: #4f46e5;
}

.stat-title {
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

/* === Daily Sales Table === */
.daily-sales-table {
    margin-top: 30px;
    width: 100%;
    border-collapse: collapse;
}

.daily-sales-table th, .daily-sales-table td {
    border: 1px solid #e5e7eb;
    padding: 10px 12px;
    text-align: center;
}

.daily-sales-table th {
    background: #f9fafb;
    font-weight: 600;
    color: #374151;
}

.arrow-up { color: #10b981; }
.arrow-down { color: #ef4444; }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Header -->
    <div class="header-card">
        <h2><i class="fas fa-tachometer-alt"></i> Performance Dashboard</h2>
    </div>

    <!-- Quick Actions -->
    <div class="section-title">
        <i class="fas fa-bolt"></i> Quick Actions
    </div>
    <div class="quick-actions-grid">
        <div class="quick-action-card">
            <i class="fas fa-plus-circle"></i>
            <a href="{{ route('sales.create') }}">Add New Sale</a>
        </div>
        <div class="quick-action-card">
            <i class="fas fa-box"></i>
            <a href="{{ route('user.products.index') }}">Manage Products</a>
        </div>
        <div class="quick-action-card">
            <i class="fas fa-file-invoice"></i>
            <a href="{{ route('user.invoices.index') }}">View Invoices</a>
        </div>
        <div class="quick-action-card">
            <i class="fas fa-truck"></i>
            <a href="{{ route('user.dispatch.index') }}">View Dispatch</a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-coins"></i></div>
            <div class="kpi-title">Total Revenue</div>
            <div class="kpi-value">NPR {{ number_format($totalSales,2) }}</div>
            <div class="kpi-subtitle">All-time sales amount</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-receipt"></i></div>
            <div class="kpi-title">Total Invoices</div>
            <div class="kpi-value">{{ number_format($totalInvoices) }}</div>
            <div class="kpi-subtitle">Processed transactions</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-star"></i></div>
            <div class="kpi-title">Top Product</div>
            <div class="kpi-value">{{ $topProduct->product->name ?? 'N/A' }}</div>
            <div class="kpi-subtitle">{{ $topProduct->total_qty ?? 0 }} units sold</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
            <div class="kpi-title">Monthly Growth</div>
            <div class="kpi-value {{ $growth >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $growth }}%
            </div>
            <div class="kpi-subtitle">
                <i class="fas fa-arrow-{{ $growth >=0 ? 'up arrow-up' : 'down arrow-down' }}"></i>
                {{ abs($growth) }}% {{ $growth >=0 ? 'increase' : 'decrease' }}
            </div>
        </div>
    </div>

    <!-- Daily Sales Table -->
    <div class="section-title">
        <i class="fas fa-calendar-day"></i> Last 30 Days Sales
    </div>
    <table class="daily-sales-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoices</th>
                <th>Total Sales (NPR)</th>
                <th>Top Product</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailySales as $day => $data)
            @php
                // Compare with previous day for trend
                $prevDay = \Carbon\Carbon::parse($day)->subDay()->toDateString();
                $trendClass = ($dailySales[$prevDay]['total'] ?? 0) < $data['total'] ? 'arrow-up' : (($dailySales[$prevDay]['total'] ?? 0) > $data['total'] ? 'arrow-down' : '');
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($day)->format('M d, Y') }}</td>
                <td>{{ $data['invoices'] }}</td>
                <td>
                    {{ number_format($data['total'],2) }}
                    @if($trendClass)
                        <i class="fas fa-arrow-{{ $trendClass == 'arrow-up' ? 'up arrow-up' : 'down arrow-down' }}"></i>
                    @endif
                </td>
                <td>{{ $data['top_product'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
@endsection
