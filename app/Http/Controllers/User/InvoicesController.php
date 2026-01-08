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
    
    public function index(Request $request)
    {
        $months = $request->months ?? 3;
        $user = Auth::user();

        $invoices = Sale::where('user_id', $user->id)
            ->where('date', '>=', Carbon::now()->subMonths($months))
            ->get();

        return view('user.invoices.index', [
            'months'         => $months,
            'totalInvoices'  => $invoices->count(),
            'totalRevenue'   => $invoices->sum('total_amount'),
            'totalTax'       => $invoices->sum('tax_amount'),
            'totalDiscount'  => $invoices->sum('discount'),
        ]);
    }

    // Server-side DataTables
    public function datatables(Request $request)
    {
        $user = Auth::user();
        $months = $request->months ?? 3;
        $startDate = Carbon::now()->subMonths($months);

        // Eager load customer, cashier (user), and items
        $invoices = Sale::with('customer', 'user', 'items')
            ->where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderByDesc('date');

        return DataTables::of($invoices)
            ->addColumn('customer', fn($sale) => $sale->customer->name ?? 'Walk-in')
            ->addColumn('cashier', fn($sale) => $sale->user->name ?? 'N/A')
            ->addColumn('items', function($sale){
              
                return $sale->items->map(fn($item) => $item->quantity.$item->product_name)->join(', ');
            })
            ->addColumn('afterdiscount', fn($sale) => number_format($sale->subtotal - $sale->discount, 2))
            ->addColumn('status', fn($sale) =>
                $sale->returned 
                    ? '<span class="badge bg-danger">Returned</span>'
                    : '<span class="badge bg-success">Completed</span>'
            )
            ->addColumn('actions', fn($sale) =>
                '<a href="'.route('user.invoices.show', $sale->id).'" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                 <a href="'.route('user.invoices.print', $sale->id).'" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i></a>'
            )
            ->editColumn('subtotal', fn($sale) => $sale->subtotal, 2)
            ->editColumn('discount', fn($sale) => $sale->discount, 2)
            ->editColumn('tax_amount', fn($sale) =>$sale->tax_amount, 2)
            ->editColumn('total_amount', fn($sale) =>$sale->total_amount, 2)
            ->editColumn('profit', fn($sale) => '<span class="'.($sale->profit >= 0 ? 'text-success' : 'text-danger').'">'.number_format($sale->profit, 2).'</span>')
            ->rawColumns(['status','actions','profit'])
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
        'payment_method'  => 'nullable|array', // now it's an array
        'payment_method.*'=> 'in:cash,qr,cheque', // validate individual values
        'payment_remarks' => 'nullable|string|max:500',
    ]);
    // Business rules
    $paymentMethod = $validated['payment_method'] ?? [];
    if ($validated['payment_status'] === 'unpaid') {
        // clear payment info if unpaid
        $paymentMethod = [];
        $validated['payment_remarks'] = null;
    }
    if ($validated['payment_status'] === 'paid' && empty($paymentMethod)) {
        return back()->withErrors(['payment_method' => 'Payment method is required for paid sales.']);
    }
    $sale->update([
        'payment_status'  => $validated['payment_status'],
        'payment_method'  => $paymentMethod, // store as array
        'payment_remarks' => $validated['payment_remarks'] ?? $sale->payment_remarks,
    ]);
    return redirect()->back()->with('success', 'Payment status updated successfully!');
}




}
