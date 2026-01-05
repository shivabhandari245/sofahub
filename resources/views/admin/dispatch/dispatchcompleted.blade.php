@extends('layouts.admin')
@section('title', 'Completed Dispatches')

@section('content')

<div class="card">
    <h2>Completed Dispatches</h2>
    <p>All dispatches that have been successfully received.</p>
</div>

<div class="card">
    <table class="table" width="100%" border="1">
        <thead>
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
        <tbody>
            @forelse($dispatches as $index => $dispatch)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dispatch->batch->product->name ?? '-' }}</td>
                    <td>{{ $dispatch->batch->product->category->name ?? '-' }}</td>
                    <td>{{ $dispatch->batch->product->quality->name ?? '-' }}</td>
                    <td>{{ $dispatch->quantity }}</td>
                    <td>{{ $dispatch->user->name ?? '-' }}</td>
                    <td>{{ $dispatch->driver ?? '-' }}</td>
                    <td>{{ $dispatch->received_date }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" align="center">No completed dispatch found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
