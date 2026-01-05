@extends('layouts.user')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/usercss/reports.css') }}" />
@endpush

@section('content')
<div class="container">

    <!-- Page Header -->
    <div class="card">
        <h2>Showroom Performance Dashboard</h2>
        <p>Comprehensive overview of monthly sales, product performance, and category trends.</p>
    </div>

    <!-- KPI Summary Cards -->
    <div class="chart-grid">
        <div class="chart-box">
            <h3>Total Sales</h3>
            <p class="kpi-value">NPR 4,790,000</p>
        </div>
        <div class="chart-box">
            <h3>Total Invoices</h3>
            <p class="kpi-value">235</p>
        </div>
        <div class="chart-box">
            <h3>Top Product</h3>
            <p class="kpi-value">Classic Sofa Set</p>
        </div>
        <div class="chart-box">
            <h3>Growth</h3>
            <p class="kpi-value">+8.5%</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="chart-grid">
        <div class="chart-box">
            <h3>Monthly Sales Overview</h3>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Top Product Categories</h3>
            <canvas id="categoryChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Sales by Payment Status</h3>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-box">
            <h3>Revenue Growth (Q1 - Q4)</h3>
            <canvas id="growthChart"></canvas>
        </div>
    </div>

    <!-- Monthly Sales Table -->
    <div class="card mt-3">
        <h3>Monthly Sales Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total Sales (NPR)</th>
                    <th>Invoices</th>
                    <th>Top Product</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>August</td>
                    <td>1,200,000</td>
                    <td>74</td>
                    <td>Classic Sofa Set</td>
                </tr>
                <tr>
                    <td>September</td>
                    <td>1,340,000</td>
                    <td>82</td>
                    <td>Recliner Chair</td>
                </tr>
                <tr>
                    <td>October</td>
                    <td>1,250,000</td>
                    <td>79</td>
                    <td>Dining Table Set</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/userjs/reports.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Example Chart.js setup
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Aug', 'Sep', 'Oct'],
        datasets: [{
            label: 'Monthly Sales',
            data: [1200000, 1340000, 1250000],
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});


</script>
@endpush