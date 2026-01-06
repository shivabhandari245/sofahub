@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">INVOICE{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</h1>
                <div class="btn-group">
                    <a href="{{ route('user.invoices.print', $sale->id) }}" 
                       class="btn btn-secondary" target="_blank">
                        <i class="fas fa-print"></i> Print Invoice
                    </a>
                       <a href="{{ route('user.invoices.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sale Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                    <th>Profit</th>
                                    <th>status</th>
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
                                    <td>{{ $item->status }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Date:</th>
                            <td>{{ $sale->date->format('M d, Y h:i A') }}</td>
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
                            <td>{{ $sale->returned_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

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
                        
                        <tr class="table-active fw-bold">
                            <th>Total Amount:</th>
                            <td class="text-end">{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Profit:</th>
                            <td class="text-end {{ $sale->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($sale->profit, 2) }}
                            </td>
                        </tr>


                         <tr>
                    <th>Payment:</th>
                    <td class="text-end">{{ $sale->payment_method ? ucfirst($sale->payment_method) : 'N/A' }}</td>
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