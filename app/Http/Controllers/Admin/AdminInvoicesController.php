<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class AdminInvoicesController extends Controller
{
    // Show main invoices page
    public function index()
    {
        return view('admin.invoice.invoices');
    }

    // Return all sales as JSON for AJAX
    public function getSalesData()
    {
        $sales = Sale::with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $sales->map(function ($sale) {
            return [
                'id' => $sale->id,
                'user' => $sale->user?->name ?? 'N/A',
                'customer' => $sale->customer?->name ?? 'Walk-in',
                'subtotal' => number_format($sale->subtotal, 2),
                'tax_rate' => $sale->tax_rate . '%',
                'tax_amount' => number_format($sale->tax_amount, 2),
                'discount' => number_format($sale->discount, 2),
                'total_amount' => number_format($sale->total_amount, 2),
                'profit' => number_format($sale->profit, 2),
                'status' => $sale->status,
                'date' => $sale->created_at->format('Y-m-d'),
            ];
        });

        return response()->json($data);
    }

    // Admin invoice detail page
    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'customer', 'user']);

        return view('admin.invoice.viewinvoices', compact('sale'));
    }
}
