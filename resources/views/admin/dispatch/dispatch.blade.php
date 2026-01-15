@extends('layouts.admin')
@section('title', 'Dispatch Management')


@push('styles')
<link rel="stylesheet" href="{{ asset('css/admincss/dispatch.css') }}" />
@endpush
@section('content')
<div class="container">
        <div class="card">
            <div class="header">
                <h1>Dispatch Management</h1>
                <div>
                    <a href="{{ url('admin/products') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <a href="{{ url('admin/dispatchcompleted') }}" class="btn btn-success" style="margin-left: 10px;">
                    <i class="fas fa-truck"></i> View Dispatched
                </a>
                </div>
            </div>
            <p>Track dispatches from admin and receive products into inventory</p>
        </div>

        <!-- Stock Requests -->
        <div class="card">

                 <!-- Admin Dispatches -->
       <div class="card">
    <h2>Dispatches for ShowRooms</h2>

<div class="search-filter" style="display:flex; gap:10px; margin-bottom:10px;">
    <input type="text" id="searchDispatches" placeholder="Search dispatches...">
    <select id="filterDispatchStatus">
        <option value="all">All Status</option>
        <option value="Pending">Pending</option>
        <option value="In Transit">In Transit</option>
    </select>
</div>

<table class="table" border="1" style="width:100%;">
    <thead>
        <tr>
            <th>SN</th>
            <th>Product</th>
            <th>Category</th>
            <th>Quality</th>
            <th>Quantity</th>
            <th>Unit Cost</th>
            <th>Total Cost</th>
            <th>ShowRoom</th>
            <th>Driver</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="dispatchTableBody">
        <!-- AJAX rows -->
    </tbody>
</table>

<div id="pagination" style="margin-top:10px;"></div>

</div>
<style>
    .modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 10px;
    width: 40%;
    position: relative;
}
.close-modal {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 25px;
    cursor: pointer;
}

</style>

   
    </div>


<!-- Distribute Batch Modal -->
<div class="modal" id="distributeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Distribute Batch</h2>
            <button type="button" class="close-modal">&times;</button>
        </div>

        <form id="distributeForm" method="POST" action="{{ url('admin/distributeBatch') }}">
            @csrf
            <!-- Hidden input for dispatch ID -->
            <input type="hidden" name="dispatch_id" id="distribute_dispatch_id">

            <div class="form-group">
                <label for="distribute_user">Select User / Showroom *</label>
                <select name="user_id" id="distribute_user" class="form-control" required>
                    <option value="">-- Select --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="distribute_quantity">Quantity *</label>
                <input type="number" name="quantity" id="distribute_quantity" class="form-control" required min="1" placeholder="Enter quantity">
                <small id="remainingQtyInfo" class="text-muted">Remaining: 0</small>
            </div>

            <div class="form-group">
                <label for="distribute_driver">Driver (Optional)</label>
                <input type="text" name="driver" id="distribute_driver" class="form-control" placeholder="Enter driver name">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Distribute Batch</button>
        </form>
    </div>
</div>

<!-- Modal Styles -->
<style>
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 10px;
    width: 40%;
    position: relative;
}

.close-modal {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 25px;
    cursor: pointer;
}
</style>



    <!-- Notification -->
    <div id="notification" class="notification"></div>
@endsection
    @push('scripts')
    <script src="{{ asset('js/admin/dispatch.js') }}"></script>
    @endpush