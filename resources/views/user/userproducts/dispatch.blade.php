@extends('layouts.user')
@section('title','Dispatch Records')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/usercss/dispatch.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                <tbody>
                    @php $i=1; @endphp
                    @foreach($dispatches as $dispatch)
                    <tr id="row{{ $dispatch->id }}">
                        <td>{{ $i++ }}</td>
                        <td>{{ $dispatch->batch->product->name ?? '-' }}</td>
                        <td>{{ $dispatch->batch->product->category->name ?? '-' }}</td>
                        <td>{{ $dispatch->batch->product->quality->name ?? '-' }}</td>
                        <td>{{ $dispatch->quantity }}</td>
                        <td>{{ number_format($dispatch->batch->expected_unit_cost,2) }}</td>
                        <td>{{ number_format($dispatch->batch->total_cost,2) }}</td>
                        <td>{{ $dispatch->driver ?? '-' }}</td>
                        <td>
                            <span class="badge 
                                    @if($dispatch->status=='Dispatched') bg-warning 
                                    @elseif($dispatch->status=='In Transit') bg-info text-dark
                                    @elseif($dispatch->status=='Delivered') bg-primary
                                    @elseif($dispatch->status=='Received') bg-success
                                    @endif">
                                {{ $dispatch->status }}
                            </span>
                        </td>
                        <td>
                            @if($dispatch->status != 'Received')
                            <button class="btn btn-success btn-sm receiveBtn" data-id="{{ $dispatch->id }}"
                                data-product="{{ $dispatch->batch->product->name }}">
                                Confirm Receive
                            </button>
                            @else
                            <span class="text-success fw-bold">Completed</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')

<script>
$(document).ready(function() {

    // CSRF token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

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
                        let row = $("#row" + res.id);

                        // Set the new status
                        let status = 'Received';

                        // Determine badge class based on status
                        let badgeClass = '';
                        switch (status) {
                            case 'Dispatched':
                                badgeClass = 'bg-warning';
                                break;
                            case 'In Transit':
                                badgeClass = 'bg-info text-dark';
                                break;
                            case 'Delivered':
                                badgeClass = 'bg-primary';
                                break;
                            case 'Received':
                                badgeClass = 'bg-success';
                                break;
                            default:
                                badgeClass = 'bg-secondary';
                        }

                        // Update the status badge dynamically
                        row.find("td:nth-child(9) span")
                            .removeClass()
                            .addClass("badge " + badgeClass)
                            .text(status);

                        // Update action column to "Completed"
                        row.find("td:nth-child(10)").html(
                            '<span class="text-success fw-bold">Completed</span>'
                            );

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
                            text: err.responseJSON?.error ||
                                'Something went wrong! Try again.',
                        });
                    }
                });
            }
        });
    });

});
</script>

@endpush