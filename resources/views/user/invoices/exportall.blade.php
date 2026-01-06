<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Invoices PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Invoices Report</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Subtotal</th>
                <th>Discount</th>
                <th>Tax</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M, Y') }}</td>
                    <td>{{ $invoice->customer->name ?? 'Walk-in' }}</td>
                    <td>
                        @foreach($invoice->items as $item)
                            {{ $item->quantity }} x {{ $item->product_name }}<br>
                        @endforeach
                    </td>
                    <td class="text-right">{{ number_format($invoice->subtotal,2) }}</td>
                    <td class="text-right">{{ number_format($invoice->discount,2) }}</td>
                    <td class="text-right">{{ number_format($invoice->tax_amount,2) }}</td>
                    <td class="text-right">{{ number_format($invoice->total_amount,2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals">
                <td colspan="4" class="text-right">Totals:</td>
                <td class="text-right">{{ number_format($invoices->sum('subtotal'),2) }}</td>
                <td class="text-right">{{ number_format($invoices->sum('discount'),2) }}</td>
                <td class="text-right">{{ number_format($invoices->sum('tax_amount'),2) }}</td>
                <td class="text-right">{{ number_format($invoices->sum('total_amount'),2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
