<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Sale;
use Yajra\DataTables\Facades\DataTables;


class InvoicesController extends Controller
{
    // Dashboard view
   public function index(Request $request)
{
    $months = $request->months ?? 3;
    $user = Auth::user();

    $fromDate = Carbon::now()->subMonths($months);

    $salesQuery = Sale::where('user_id', $user->id)
        ->where('created_at', '>=', $fromDate);

    $totalInvoices = (clone $salesQuery)->count();
    $totalRevenue  = (clone $salesQuery)->sum('total_amount');
    $totalTax      = (clone $salesQuery)->sum('tax_amount');
    $totalDiscount = (clone $salesQuery)->sum('discount');

    $totalPaid = (clone $salesQuery)
        ->where('payment_status', 'paid')
        ->sum('total_amount');

    $totalUnpaid = (clone $salesQuery)
        ->where('payment_status', 'unpaid')
        ->sum('total_amount');

    $totalPartial = (clone $salesQuery)
        ->where('payment_status', 'partially_paid')
        ->sum('total_amount');

    return view('user.invoices.index', compact(
        'months',
        'totalInvoices',
        'totalRevenue',
        'totalTax',
        'totalDiscount',
        'totalPaid',
        'totalUnpaid',
        'totalPartial'
    ));
}

   
public function datatables(Request $request)
{
    $user = Auth::user();
    $months = $request->months ?? 3; // default last 3 months
    $startDate = Carbon::now()->subMonths($months)->startOfDay();

    // Base query: only needed records
    $query = Sale::with('customer', 'user', 'items')
        ->where('user_id', $user->id)
        ->where('date', '>=', $startDate)
        ->orderByDesc('date');

    return DataTables::of($query)
        // Apply search filter
        ->filter(function ($query) use ($request) {
            if ($search = $request->search['value'] ?? null) {
                $query->where(function($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('payment_method', 'like', "%{$search}%")
                      ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('user', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
                });
            }

            // Additional filters (e.g., month filter) if needed in the future
            if ($months = $request->months ?? null) {
                $startDate = Carbon::now()->subMonths($months)->startOfDay();
                $query->where('date', '>=', $startDate);
            }
        })
        ->editColumn('id', fn($sale) => str_pad($sale->id, 6, '0', STR_PAD_LEFT))
        ->addColumn('customer', fn($sale) => $sale->customer->name ?? 'Walk-in')
        ->addColumn('cashier', fn($sale) => $sale->user->name ?? 'N/A')
        ->addColumn('items', fn($sale) => $sale->items->map(fn($item) => $item->quantity.$item->product_name)->join(', '))
        ->editColumn('discount', fn($sale) => number_format($sale->discount, 2))
        ->editColumn('tax_amount', fn($sale) => number_format($sale->tax_amount, 2))
        ->editColumn('total_amount', fn($sale) => number_format($sale->total_amount, 2))
        ->addColumn('profitafterdiscount', fn($sale) => number_format($sale->profit, 2))
        ->addColumn('payment_status', fn($sale) => ucfirst($sale->payment_status))
        ->addColumn('actions', fn($sale) =>
            '<a href="'.route('user.invoices.show', $sale->id).'" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
             <a href="'.route('user.invoices.print', $sale->id).'" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i></a>'
        )
        ->rawColumns(['actions'])
        ->make(true);
}



    public function show($id)
{
    $sale = Sale::with('customer', 'items')->where('user_id', Auth::id())->findOrFail($id);

    return view('user.invoices.show', [
        'sale' => $sale
    ]);
}



    public function print($id)
{
    $sale = Sale::with('customer', 'items')->where('user_id', Auth::id())->findOrFail($id);

    return view('user.invoices.print', [
        'sale' => $sale
    ]);
}


public function downloadAll(Request $request)
{
    $user = Auth::user();
    $months = $request->months ?? 3;

    $invoices = Sale::with('customer', 'items')
        ->where('user_id', $user->id)
        ->where('date', '>=', Carbon::now()->subMonths($months))
        ->get();

    if($invoices->isEmpty()) {
        return redirect()->back()->with('error', 'No invoices to download.');
    }

    $pdf = Pdf::loadView('user.invoices.exportall', [
        'invoices' => $invoices
    ]);

    return $pdf->download('invoices_' . now()->format('Ymd_His') . '.pdf');
}

// In InvoicesController.php
public function updatePaymentStatus(Request $request, Sale $sale)
{
    $validated = $request->validate([
        'payment_status'  => 'required|in:paid,partial,unpaid',
        'payment_method'  => 'nullable|string|max:255',
        'payment_remarks' => 'nullable|string|max:500',
    ]);

    $sale->update([
        'payment_status'  => $validated['payment_status'],
        'payment_method'  => $validated['payment_method'] ?? $sale->payment_method,
        'payment_remarks' => $validated['payment_remarks'] ?? $sale->payment_remarks,
    ]);

    return redirect()->back()->with('success', 'Payment status updated successfully!');
}



}
