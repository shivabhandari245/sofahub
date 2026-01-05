@extends('layouts.admin')
@section('title', 'Invoices Management')

@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/invoices.css') }}" />
<style>
.summary {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.summary-card {
    background: #f5f5f5;
    padding: 15px;
    border-radius: 8px;
    flex: 1;
    text-align: center;
}

.top-actions {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    align-items: center;
}

.search-filter input,
.search-filter select {
    padding: 7px;
    margin-left: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th,
table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
    white-space: nowrap;
}

.btn-view {
    background-color: #2196F3;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    text-decoration: none;
}

.btn-view:hover {
    background-color: #1976D2;
}
</style>
@endpush

<div class="card">
    <h2>Invoices Management</h2>
    <p>Track, manage, and monitor showroom and factory invoices efficiently.</p>
</div>

<div class="summary">
    <div class="summary-card">
        <h3>Total Invoices</h3>
        <p id="totalInv">0</p>
    </div>
    <div class="summary-card">
        <h3>Paid</h3>
        <p id="paidInv">0</p>
    </div>
    <div class="summary-card">
        <h3>Pending</h3>
        <p id="pendingInv">0</p>
    </div>
    <div class="summary-card">
        <h3>Unpaid</h3>
        <p id="unpaidInv">0</p>
    </div>
</div>

<div class="top-actions">
    <div class="search-filter">
        <input type="text" id="searchInput" placeholder="🔍 Search by Client or User">
        <select id="filterStatus">
            <option value="all">All Status</option>
            <option value="Paid">Paid</option>
            <option value="Pending">Pending</option>
            <option value="Unpaid">Unpaid</option>
        </select>

        <button class="btn-add" id="toggleFormBtn">
            <i class="bi bi-plus-circle"></i> Create Invoice
        </button>
    </div>
</div>

<div class="card">
    <h3>Recent Invoices</h3>

    <div class="table-container">
        <table class="data-table" id="invoiceTable">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>User</th>
                    <th>Client</th>
                    <th>Subtotal</th>
                    <th>Tax Rate</th>
                    <th>Tax Amount</th>
                    <th>Discount</th>
                    <th>Total Amount</th>
                    <th>Profit</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="invoiceBody">
                <!-- JS populated -->
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const invoiceBody = document.getElementById('invoiceBody');
    const totalInv = document.getElementById('totalInv');
    const paidInv = document.getElementById('paidInv');
    const pendingInv = document.getElementById('pendingInv');
    const unpaidInv = document.getElementById('unpaidInv');
    const filterStatus = document.getElementById('filterStatus');
    const searchInput = document.getElementById('searchInput');

    async function fetchInvoices() {
        try {
            const res = await fetch('{{ url("/admin/allinvoices") }}');
            const data = await res.json();
            renderTable(data);
            updateSummary(data);
        } catch (err) {
            console.error('Error fetching invoices:', err);
        }
    }

    function renderTable(data) {
        const filter = filterStatus.value.toLowerCase();
        const search = searchInput.value.toLowerCase();

        const filtered = data.filter(inv => {
            const matchStatus = filter === 'all' || (inv.status || '').toLowerCase() === filter;
            const matchSearch = (inv.customer || '').toLowerCase().includes(search) || (inv.user || '')
                .toLowerCase().includes(search);
            return matchStatus && matchSearch;
        });

        invoiceBody.innerHTML = '';
        filtered.forEach(inv => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${inv.id}</td>
                <td>${inv.user}</td>
                <td>${inv.customer}</td>
                <td>${inv.subtotal}</td>
                <td>${inv.tax_rate}</td>
                <td>${inv.tax_amount}</td>
                <td>${inv.discount}</td>
                <td>${inv.total_amount}</td>
                <td>${inv.profit}</td>
                <td>${inv.status}</td>
                <td>${inv.date}</td>
                <td><a href="/admin/invoices/view/${inv.id}" class="btn-view">View</a></td>
            `;
            invoiceBody.appendChild(row);
        });
    }

    function updateSummary(data) {
        totalInv.textContent = data.length;
        paidInv.textContent = data.filter(d => d.status === 'Paid').length;
        pendingInv.textContent = data.filter(d => d.status === 'Pending').length;
        unpaidInv.textContent = data.filter(d => d.status === 'Unpaid').length;
    }

    filterStatus.addEventListener('change', fetchInvoices);
    searchInput.addEventListener('input', fetchInvoices);

    fetchInvoices();
});
</script>
@endpush