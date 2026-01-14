<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Invoices PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .report-info {
            text-align: center;
            margin-bottom: 15px;
            color: #666;
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
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        .page-break {
            page-break-after: always;
        }
        .status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <h2>Invoices Report</h2>
    
    <div class="report-info">
        <p>Generated on: {{ now()->format('d M, Y h:i A') }}</p>
        <p>Period: Last {{ $months }} month(s)</p>
        <p>Total Invoices: {{ $invoices->count() }}</p>
    </div>
    
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
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @if($invoices->count() > 0)
                @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->id }}</td>
                        <td>{{ $invoice->created_at ? $invoice->created_at->format('d M, Y') : 'N/A' }}</td>
                        <td>{{ $invoice->customer->name ?? 'Walk-in' }}</td>
                        <td>
                            @if($invoice->items && $invoice->items->count() > 0)
                                @foreach($invoice->items as $item)
                                    {{ $item->quantity ?? 0 }} x {{ $item->product_name ?? 'N/A' }}<br>
                                @endforeach
                            @else
                                No items
                            @endif
                        </td>
                        <td class="text-right">RS {{ number_format($invoice->subtotal, 2) }}</td>
                        <td class="text-right">RS {{ number_format($invoice->discount, 2) }}</td>
                        <td class="text-right">RS {{ number_format($invoice->tax_amount, 2) }}</td>
                        <td class="text-right">RS {{ number_format($invoice->total_amount, 2) }}</td>
                        <td>
                            @php
                                $statusClass = 'status-pending';
                                if($invoice->status == 'Paid') $statusClass = 'status-paid';
                                if($invoice->status == 'Unpaid') $statusClass = 'status-unpaid';
                            @endphp
                            <span class="status {{ $statusClass }}">
                                {{ $invoice->status ?? 'Pending' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" class="no-data">
                        No invoices found for the selected period.
                    </td>
                </tr>
            @endif
        </tbody>
        @if($invoices->count() > 0)
            <tfoot>
                <tr class="totals">
                    <td colspan="4" class="text-right">Grand Totals:</td>
                    <td class="text-right">RS {{ number_format($invoices->sum('subtotal'), 2) }}</td>
                    <td class="text-right">RS {{ number_format($invoices->sum('discount'), 2) }}</td>
                    <td class="text-right">RS {{ number_format($invoices->sum('tax_amount'), 2) }}</td>
                    <td class="text-right">RS {{ number_format($invoices->sum('total_amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
    
    @if($invoices->count() > 0)
        <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
            <p>Summary:</p>
            <p>Total Invoices: {{ $invoices->count() }}</p>
            <p>Paid: {{ $invoices->where('status', 'Paid')->count() }}</p>
            <p>Unpaid: {{ $invoices->where('status', 'Unpaid')->count() }}</p>
            <p>Pending: {{ $invoices->where('status', 'Pending')->count() }}</p>
        </div>
    @endif
</body>
</html>