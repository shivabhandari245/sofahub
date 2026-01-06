@extends('layouts.user')
@section('title','Dispatch Records')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/usercss/dispatch.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@endpush

@section('content')
<div class="container">

    <div class="card mt-3">
        <div class="header d-flex justify-content-between align-items-center">
            <h1>My Dispatch Records</h1>
            <a href="{{ url('user/products') }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>
        <p>View items admins have dispatched to your showroom and confirm the stock arrival.</p>
    </div>

    <div class="card mt-3">
        <h2>Incoming Stock</h2>
        <div class="table-responsive">
            <table class="table table-bordered" id="userDispatchTable">
                <thead class="table-light">
                    <tr>
                        <th>SN</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Quality</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total Cost</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th width="140px">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
$(document).ready(function() {

    // CSRF token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize server-side DataTable
    var table = $('#userDispatchTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('user.dispatch.serverSide') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'product', name: 'product' },
            { data: 'category', name: 'category' },
            { data: 'quality', name: 'quality' },
            { data: 'quantity', name: 'quantity' },
            { data: 'unit_cost', name: 'unit_cost' },
            { data: 'total_cost', name: 'total_cost' },
            { data: 'driver', name: 'driver' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50]
    });

    // Confirm Receive button
    $(document).on('click', '.receiveBtn', function() {
        let dispatchId = $(this).data('id');
        let productName = $(this).data('product');

        Swal.fire({
            title: 'Confirm Receipt',
            text: `Are you sure you have received "${productName}"?`,
            input: 'text',
            inputLabel: 'Remarks (optional)',
            showCancelButton: true,
            confirmButtonText: 'Yes, confirm',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('user.dispatch.confirmReceive') }}",
                    type: "POST",
                    data: {
                        dispatch_id: dispatchId,
                        remarks: result.value
                    },
                    success: function(res) {
                        // Reload table row after update
                        table.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Confirmed!',
                            text: `Stock for "${productName}" has been updated.`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err.responseJSON?.error || 'Something went wrong! Try again.',
                        });
                    }
                });
            }
        });
    });

});
</script>
@endpush
