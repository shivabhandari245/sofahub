@extends('layouts.admin')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    Invoice #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
                </h1>
                <div class="btn-group">
                    <a href="{{ url('admin/invoices') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- SALE ITEMS --}}
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sale Items</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                                <th>Profit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->subtotal, 2) }}</td>
                                <td class="{{ $item->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($item->profit, 2) }}
                                </td>
                                <td>{{ ucfirst($item->status) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SALE INFO --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Date:</th>
                            <td>{{ $sale->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Cashier:</th>
                            <td>{{ $sale->user->name }}</td>
                        </tr>
                        <tr>
                            <th>Customer:</th>
                            <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($sale->returned)
                                    <span class="badge bg-danger">Returned</span>
                                @else
                                    <span class="badge bg-success">Completed</span>
                                @endif
                            </td>
                        </tr>
                        @if($sale->returned)
                        <tr>
                            <th>Return Reason:</th>
                            <td>{{ $sale->return_reason }}</td>
                        </tr>
                        <tr>
                            <th>Returned At:</th>
                            <td>{{ $sale->returned_at?->format('M d, Y h:i A') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- PAYMENT SUMMARY --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Subtotal:</th>
                            <td class="text-end">{{ number_format($sale->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Tax ({{ $sale->tax_rate }}%):</th>
                            <td class="text-end">{{ number_format($sale->tax_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td class="text-end">-{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        <tr class="fw-bold table-active">
                            <th>Total:</th>
                            <td class="text-end">{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Profit:</th>
                            <td class="text-end {{ $sale->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($sale->profit, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
