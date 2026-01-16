@extends('layouts.user')

@section('content')
<div class="container mt-3 mb-3 p-0" style="max-width: 600px; font-family: Arial, sans-serif;">
    
    <!-- Header -->
    <div class="text-center mb-3">
        <h3 class="mb-0">SOFAHUB</h3>
        <small>Invoice Receipt</small><br>
        <strong>#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</strong>
    </div>

    <!-- Print Button -->
    <div class="text-end mb-2 no-print">
        <button class="btn btn-sm btn-secondary" onclick="window.print()">Print</button>
    </div>

    <!-- Customer & Sale Info -->
    <div class="row mb-2">
        <div class="col-6">
            <strong>Customer:</strong><br>
            @if($sale->customer)
                {{ $sale->customer->name }}<br>
                {{ $sale->customer->email ?? 'N/A' }}<br>
                {{ $sale->customer->phone ?? 'N/A' }}<br>
                {{ $sale->customer->address ?? 'N/A' }}
            @else
                Walk-in Customer
            @endif
        </div>
        <div class="col-6 text-end">
            <strong>Sale Info:</strong><br>
            Date: {{ $sale->created_at->format('M d, Y h:i A') }}<br>
            Cashier: {{ $sale->user->name }}<br>
            Status: 
            <span class="badge {{ $sale->status === 'returned' ? 'bg-danger' : 'bg-success' }}">
                {{ ucfirst($sale->status) }}
            </span>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-2">
        <table class="table table-bordered table-sm mb-0">
            <thead class="table-light text-center">
                <tr>
                    <th>S.N</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $i => $item)
                <tr class="text-center">
                    <td>{{ $i + 1 }}</td>
                    <td class="text-start">{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="row justify-content-end mb-2">
        <div class="col-6">
            <table class="table table-sm mb-0">
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
                 <tr>
    <th>Payment Status:</th>
    <td class="text-end">
        <span class="badge 
            {{ $sale->payment_status === 'paid' ? 'bg-success' : ($sale->payment_status === 'partially_paid' ? 'bg-warning' : 'bg-danger') }}">
            {{ ucfirst(str_replace('_', ' ', $sale->payment_status)) }}
        </span>
    </td>
</tr>
<tr>
    <th>Payment Method:</th>
    <td class="text-end">
        @php
        $icons = ['cash'=>'💵', 'qr'=>'📱', 'cheque'=>'🧾'];
        @endphp
        @if(!empty($sale->payment_method))
            @foreach($sale->payment_method as $method)
                <span>{{ $icons[$method] ?? '' }} {{ ucfirst($method) }}</span>@if(!$loop->last), @endif
            @endforeach
        @else
            N/A
        @endif
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

    <!-- Footer -->
    <div class="text-center mt-3">
        <small>Thank you for your purchase!</small><br>
      
    </div>

    <!-- Print Styles -->
    <style>
        body { font-size: 12px; }
        .table th, .table td { padding: 0.35rem; vertical-align: middle; }
        .container { max-width: 600px; }
        .badge { font-size: 0.8rem; }
        @media print {
            .no-print { display: none !important; }
            table { page-break-inside: avoid; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</div>
@endsection
