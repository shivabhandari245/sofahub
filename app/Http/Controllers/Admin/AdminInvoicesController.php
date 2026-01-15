<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminInvoicesController extends Controller
{
    public function index(Request $request)
    {
        $months = $request->get('months', 1);
        
        // Calculate date range
        $dateFrom = now()->subMonths($months);
        
        // Get sales data
        $sales = Sale::with(['customer', 'user'])
            ->where('created_at', '>=', $dateFrom)
            ->get();
        
        // Summary calculations
        $totalInvoices = $sales->count();
        $totalRevenue = $sales->sum('total_amount');
        $totalTax = $sales->sum('tax_amount');
        $totalDiscount = $sales->sum('discount');
        
        // Payment status summary
        $totalPaidCount = $sales->where('status', 'Paid')->count();
        $totalPaidAmount = $sales->where('status', 'Paid')->sum('total_amount');
        
        $totalUnpaidCount = $sales->where('status', 'Unpaid')->count();
        $totalUnpaidAmount = $sales->where('status', 'Unpaid')->sum('total_amount');
        
        $totalPartialCount = $sales->where('status', 'Pending')->count();
        $totalPartialAmount = $sales->where('status', 'Pending')->sum('total_amount');
        
        // Calculate remaining amount for partial payments
        $totalPartialRemaining = $sales->where('status', 'Pending')->sum(function($sale) {
            // Assuming you have a paid_amount field, otherwise use total_amount
            return $sale->total_amount - ($sale->paid_amount ?? 0);
        });
        
        return view('admin.invoice.invoices', compact(
            'months',
            'totalInvoices',
            'totalRevenue',
            'totalTax',
            'totalDiscount',
            'totalPaidCount',
            'totalPaidAmount',
            'totalUnpaidCount',
            'totalUnpaidAmount',
            'totalPartialCount',
            'totalPartialAmount',
            'totalPartialRemaining'
        ));
    }

    public function getSalesData(Request $request)
    {
        $months = $request->get('months', 1);
        $dateFrom = now()->subMonths($months);
        $paymentStatus = $request->get('payment_status');
        
        $query = Sale::with(['customer', 'user'])
            ->where('created_at', '>=', $dateFrom);
        
        if ($paymentStatus && $paymentStatus !== 'all') {
            $query->where('status', $paymentStatus);
        }
        
        return DataTables::eloquent($query)
            ->addColumn('customer', function($sale) {
                return $sale->customer?->name ?? 'Walk-in';
            })
            ->addColumn('cashier', function($sale) {
                return $sale->user?->name ?? 'N/A';
            })
            ->addColumn('date', function($sale) {
                return $sale->created_at->format('Y-m-d');
            })
            ->addColumn('actions', function($sale) {
                return '
                    <div class="action-btns">
                        <a href="'.route('admin.invoices.show', $sale->id).'" 
                            class="btn btn-sm btn-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        <a href="'.url("/admin/{$sale->id}/download").'" 
                           class="btn btn-sm btn-danger" title="Download PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </div>
                ';
            })
            ->editColumn('discount', function($sale) {
                return 'RS ' . number_format($sale->discount, 2);
            })
            ->editColumn('tax_amount', function($sale) {
                return 'RS ' . number_format($sale->tax_amount, 2);
            })
            ->editColumn('total_amount', function($sale) {
                return 'RS ' . number_format($sale->total_amount, 2);
            })
            ->editColumn('profit', function($sale) {
                return 'RS ' . number_format($sale->profit, 2);
            })
            ->rawColumns(['actions'])
            ->make(true);
    }


public function downloadAll(Request $request)
{
    $months = $request->get('months', 1);
    $paymentStatus = $request->get('payment_status');
    $dateFrom = now()->subMonths($months);
    
    $query = Sale::with(['customer', 'items'])
        ->where('created_at', '>=', $dateFrom);
    
    if ($paymentStatus && $paymentStatus !== 'all') {
        $query->where('status', $paymentStatus);
    }
    
    // Rename to 'invoices' to match your view
    $invoices = $query->orderBy('created_at', 'desc')->get();
    
    // Return the view with 'invoices' variable
    return view('admin.invoice.exportpdf', compact('invoices', 'months'));
}

     public function view($id)
{
    $sale = Sale::with('customer', 'items')->findOrFail($id);

    return view('admin.invoice.viewinvoices', [
        'sale' => $sale
    ]);
}
    
    public function download(Sale $sale)
    {
        $sale->load(['items.product', 'customer', 'user']);
        
        // For now, show a view
        // You can implement PDF generation here
        return view('admin.invoice.single-pdf', compact('sale'));
        
        /* Uncomment to implement actual PDF
        $pdf = PDF::loadView('admin.invoice.single-pdf', compact('sale'));
        return $pdf->download('invoice-'.$sale->id.'.pdf');
        */
    }
}