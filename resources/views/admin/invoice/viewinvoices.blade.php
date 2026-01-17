@extends('layouts.admin')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                Invoice #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
            </h1>
            <div class="btn-group">
             <a href="{{ url('admin/printinvoice', $sale->id) }}" 
   class="btn btn-secondary" target="_blank">
    <i class="fas fa-print"></i> Print
</a>

                <a href="{{ url('admin/invoice') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
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
                                    <td>{{ ucfirst($item->status ?? 'sold') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SALE INFO --}}
        <div class="col-md-4">

            {{-- GENERAL INFO --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Date:</th>
                            <td>{{ $sale->date?->format('M d, Y h:i A') }}</td>
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
                            <th>Sale Status:</th>
                            <td>
                                <span class="badge {{ $sale->returned ? 'bg-danger' : 'bg-success' }}">
                                    {{ $sale->returned ? 'Returned' : 'Completed' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <td>
                                <span class="badge
                                    {{ $sale->payment_status === 'paid' ? 'bg-success' :
                                       ($sale->payment_status === 'partial' ? 'bg-warning' : 'bg-danger') }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- PAYMENT / PROFIT SUMMARY --}}
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
                            <th>Discount:</th>
                            <td class="text-end">-{{ number_format($sale->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>After Discount:</th>
                            <td class="text-end">{{ number_format($sale->afterdiscount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Tax ({{ $sale->tax_rate }}%):</th>
                            <td class="text-end">{{ number_format($sale->tax_amount, 2) }}</td>
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
                        <tr>
                            <th>Payment Method:</th>
                            <td class="text-end">
                                {{ !empty($sale->payment_method)
                                    ? ucfirst(implode(', ', $sale->payment_method))
                                    : 'N/A' }}
                            </td>
                        </tr>
                        @if($sale->payment_remarks)
                            <tr>
                                <th>Remarks:</th>
                                <td class="text-end">{{ $sale->payment_remarks }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
