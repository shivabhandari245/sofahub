@extends('layouts.user')

<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        font-size: 14px;
    }

    .table th,
    .table td {
        vertical-align: middle;
    }

    @media print {
        .no-print {
            display: none;
        }
    }
    </style>
</head>

@section('content')

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>SOFAHUB INVOICE BILL RECEIPT{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</h2>
            <button class="btn btn-secondary no-print" onclick="window.print()">Print</button>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Customer</h5>
                <p>
                    {{ $sale->customer->name ?? 'Walk-in Customer' }}<br>
                    @if($sale->customer)
                    {{ $sale->customer->email ?? '' }}<br>
                    {{ $sale->customer->phone ?? '' }}
                    @endif
                </p>
            </div>
            <div class="col-md-6 text-end">
                <h5>Sale Info</h5>
                <p>
                    Date: {{ $sale->created_at->format('M d, Y h:i A') }}<br>
                    Cashier: {{ $sale->user->name }}<br>
                    Status:
                    @if($sale->returned)
                    <span class="badge bg-danger">Returned</span>
                    @else
                    <span class="badge bg-success">Completed</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                        <th>status</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->subtotal, 2) }}</td>
                        <td>{{ $item->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
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
                        <th>Tax ({{ $sale->tax_rate }}%):</th>
                        <td class="text-end">{{ number_format($sale->tax_amount, 2) }}</td>
                    </tr>
                    
                    <tr class="fw-bold table-active">
                        <th>Total:</th>
                        <td class="text-end">{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                    <tr>

                    </tr>
                </table>
            </div>
        </div>

        <div class="text-center mt-5">
            <small>Thank you for your purchase!</small>
        </div>
    </div>
</body>

</html>

@endsection