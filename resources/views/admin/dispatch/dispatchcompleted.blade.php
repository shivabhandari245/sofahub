@extends('layouts.admin')
@section('title', 'Completed Dispatches')

@section('content')

<div class="card" style="padding:20px; margin-bottom:20px;">
    <h2>Completed Dispatches</h2>
    <p>All dispatches that have been successfully received.</p>

    <div style="margin-top:15px; display:flex; gap:10px;">
        <input type="text" id="searchCompleted" placeholder="Search by product, showroom, or driver..."
               class="form-control" style="flex:1; padding:8px;">
        <button id="searchBtn" class="btn btn-primary">Search</button>
    </div>
</div>

<div class="card" style="padding:20px; overflow-x:auto;">
    <table class="table" width="100%">
        <thead style="background:#f5f5f5; text-align:left;">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Category</th>
                <th>Quality</th>
                <th>Quantity</th>
                <th>Showroom</th>
                <th>Driver</th>
                <th>Received Date</th>
            </tr>
        </thead>
        <tbody id="completedDispatchBody">
            <tr><td colspan="8" class="text-center">Loading...</td></tr>
        </tbody>
    </table>

    <div id="completedPagination" style="margin-top:15px;"></div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;

function loadCompletedDispatches(page = 1) {
    currentPage = page;

    $.get('/admin/completedDispatchesajax', {
        page: page,
        search: $('#searchCompleted').val()
    }, function(res) {
        let rows = '';
        let sn = (res.current_page - 1) * res.per_page + 1;

        if (res.data.length === 0) {
            rows = `<tr><td colspan="8" class="text-center">No completed dispatch found</td></tr>`;
        } else {
            res.data.forEach(d => {
                rows += `
                <tr>
                    <td>${sn++}</td>
                    <td>${d.batch?.product?.name ?? '-'}</td>
                    <td>${d.batch?.product?.category?.name ?? '-'}</td>
                    <td>${d.batch?.product?.quality?.name ?? '-'}</td>
                    <td>${d.quantity}</td>
                    <td>${d.user?.name ?? '-'}</td>
                    <td>${d.driver ?? '-'}</td>
                    <td>${d.received_date ?? '-'}</td>
                </tr>`;
            });
        }

        $('#completedDispatchBody').html(rows);
        renderPagination(res.current_page, res.last_page);
    });
}

function renderPagination(current, last) {
    if (last <= 1) {
        $('#completedPagination').html('');
        return;
    }

    let html = `<button class="btn btn-sm" ${current === 1 ? 'disabled' : ''} onclick="loadCompletedDispatches(${current - 1})">Prev</button>`;

    for (let i = 1; i <= last; i++) {
        html += `<button class="btn btn-sm ${i === current ? 'btn-primary' : ''}" 
                    onclick="loadCompletedDispatches(${i})">${i}</button>`;
    }

    html += `<button class="btn btn-sm" ${current === last ? 'disabled' : ''} onclick="loadCompletedDispatches(${current + 1})">Next</button>`;

    $('#completedPagination').html(html);
}

// Initial load
loadCompletedDispatches();

// Search
$('#searchBtn').click(function() {
    loadCompletedDispatches(1);
});
$('#searchCompleted').on('keyup', function(e) {
    if(e.key === 'Enter') loadCompletedDispatches(1);
});
</script>
@endpush
<style>
        .card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .table {
        border-collapse: collapse;
        width: 100%;
    }

    .table th, .table td {
        padding: 10px;
        text-align: left;
    }

    .table th {
        background: #f0f0f0;
        font-weight: bold;
    }

    .table tr:nth-child(even) {
        background: #fafafa;
    }

    .table tr:hover {
        background: #f1f7ff;
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
        padding: 7px 15px;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-primary:hover {
        background-color: #0069d9;
    }

    input.form-control {
        border: 1px solid #ccc;
        border-radius: 5px;
    }

</style>