<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    // Get all invoices for this user in the selected months
    $invoices = Sale::where('user_id', $user->id)
        ->where('date', '>=', Carbon::now()->subMonths($months)->startOfMonth())
        ->get();

    // Total stats
    $totalInvoices = $invoices->count();
    $totalRevenue = $invoices->sum('total_amount');
    $totalTax = $invoices->sum('tax_amount');
    $totalDiscount = $invoices->sum('discount');

    // Paid invoices (fully paid)
    $paidInvoices = $invoices->where('payment_status', 'paid');
    $totalPaidCount = $paidInvoices->count();
    $totalPaidAmount = $paidInvoices->sum('total_amount');

    // Unpaid invoices (not paid at all)
    $unpaidInvoices = $invoices->where('payment_status', 'unpaid');
    $totalUnpaidCount = $unpaidInvoices->count();
    $totalUnpaidAmount = $unpaidInvoices->sum('total_amount');

    // Partially paid invoices
    $partialInvoices = $invoices->where('payment_status', 'partially_paid');
    $totalPartialCount = $partialInvoices->count();
    $totalPartialAmount = $partialInvoices->sum('paid_amount'); // received amount
    $totalPartialRemaining = $partialInvoices->sum(fn($invoice) => $invoice->total_amount - $invoice->paid_amount);

    return view('user.invoices.index', [
        'months'                 => $months,
        'totalInvoices'          => $totalInvoices,
        'totalRevenue'           => $totalRevenue,
        'totalTax'               => $totalTax,
        'totalDiscount'          => $totalDiscount,
        'totalPaidCount'         => $totalPaidCount,
        'totalPaidAmount'        => $totalPaidAmount,
        'totalUnpaidCount'       => $totalUnpaidCount,
        'totalUnpaidAmount'      => $totalUnpaidAmount,
        'totalPartialCount'      => $totalPartialCount,
        'totalPartialAmount'     => $totalPartialAmount,
        'totalPartialRemaining'  => $totalPartialRemaining,
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
             if ($request->filled('payment_status')) {
        $invoices->where('payment_status', $request->payment_status);
    }

        return DataTables::of($invoices)
            ->addColumn('customer', fn($sale) => $sale->customer->name ?? 'Walk-in')
            ->addColumn('cashier', fn($sale) => $sale->user->name ?? 'N/A')
            ->addColumn('items', function($sale){
              
                return $sale->items->map(fn($item) => $item->quantity.$item->product_name)->join(', ');
            })
            ->addColumn('afterdiscount', fn($sale) =>$sale->subtotal - $sale->discount)
            ->addColumn('status', fn($sale) =>
                $sale->returned 
                    ? '<span class="badge bg-danger">Returned</span>'
                    : '<span class="badge bg-success">Completed</span>'
            )
            ->addColumn('actions', fn($sale) =>
                '<a href="'.route('user.invoices.show', $sale->id).'" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                 <a href="'.route('user.invoices.print', $sale->id).'" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i></a>'
            )
            ->editColumn('subtotal', fn($sale) => $sale->subtotal)
            ->editColumn('discount', fn($sale) => $sale->discount)
            ->editColumn('tax_amount', fn($sale) =>$sale->tax_amount)
            ->editColumn('total_amount', fn($sale) =>$sale->total_amount)
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
