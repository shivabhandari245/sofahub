<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoices Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            color: #0d6efd;
        }
        .meta {
            margin-top: 5px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 6px;
        }
        table th {
            background: #f5f5f5;
        }
        .totals {
            margin-top: 20px;
            width: 40%;
            float: right;
        }
        .totals td {
            padding: 6px;
        }
        .totals tr:last-child td {
            font-weight: bold;
            background: #f5f5f5;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>{{ $user->name }}</h2>
    <div class="meta">
        <div>Invoices Report</div>
        <div>
            Period: {{ $date_range['start'] }} – {{ $date_range['end'] }}
        </div>
        <div>Generated: {{ now()->format('F d, Y h:i A') }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Invoice ID</th>
            <th>Customer</th>
            <th>Product</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Subtotal</th>
            <th style="text-align:right;">Tax</th>
            <th style="text-align:right;">Discount</th>
            <th style="text-align:right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $index => $invoice)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($invoice->date)->format('Y-m-d') }}</td>
            <td>{{ $invoice->id }}</td>
            <td>{{ $invoice->customer_name }}</td>
            <td>{{ $invoice->product_name }}</td>
            <td style="text-align:center;">{{ $invoice->quantity }}</td>
            <td style="text-align:right;">RS {{ number_format($invoice->subtotal, 2) }}</td>
            <td style="text-align:right;">RS {{ number_format($invoice->tax_amount, 2) }}</td>
            <td style="text-align:right;">RS {{ number_format($invoice->discount, 2) }}</td>
            <td style="text-align:right;">RS {{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals">
    <tr>
        <td>Total Revenue</td>
        <td style="text-align:right;">
            RS {{ number_format($totalRevenue, 2) }}
        </td>
    </tr>
    <tr>
        <td>Total Tax</td>
        <td style="text-align:right;">
            RS {{ number_format($totalTax, 2) }}
        </td>
    </tr>
    <tr>
        <td>Total Discount</td>
        <td style="text-align:right;">
            RS {{ number_format($totalDiscount, 2) }}
        </td>
    </tr>
</table>

<div style="clear: both"></div>

<div class="footer">
    This is a system-generated report.<br>
    {{ $user->name }}
</div>

</body>
</html>
