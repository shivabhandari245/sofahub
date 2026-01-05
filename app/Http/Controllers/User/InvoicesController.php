<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Sale;
use Illuminate\Support\Str;

class InvoicesController extends Controller
{
   
    public function index(Request $request)
    {
        $user = Auth::user();
        $months = $request->months ?? 3;

        $invoices = $this->fetchInvoices($user->id, $months);

        return view('user.invoices.index', [
            'invoices'       => $invoices->take(10),
            'allInvoices'    => $invoices,
            'months'         => $months,
            'totalInvoices'  => $invoices->count(),
            'totalRevenue'   => $invoices->sum('total_amount'),
            'totalTax'       => $invoices->sum('tax_amount'),
            'totalDiscount'  => $invoices->sum('discount'),
            'user'           => $user
        ]);
    }

   
    
private function fetchInvoices($userId, $months)
{
    $startDate = Carbon::now()->subMonths($months);

    $invoices = Sale::with('customer')
        ->where('user_id', $userId)
        ->where('date', '>=', $startDate)
        ->orderByDesc('date')
        ->get();

    foreach ($invoices as $invoice) {
        $this->formatInvoice($invoice);
    }

    return $invoices;
}

  
    public function search(Request $request)
    {
        $user = Auth::user();
        $query = $request->q;

        $results = DB::table('sales')
            ->where('user_id', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('customer_name', 'like', "%$query%")
                  ->orWhere('id', 'like', "%$query%")
                  ->orWhere('customer_email', 'like', "%$query%")
                  ->orWhere('customer_phone', 'like', "%$query%");
            })
            ->orderByDesc('date')
            ->limit(20)
            ->get();

        foreach ($results as $invoice) {
            $this->formatInvoice($invoice);
        }

        return response()->json($results);
    }

   
    public function getInvoiceDetails($id)
    {
        $user = Auth::user();

 $invoice = Sale::with('customer')
    ->where('user_id', $user->id)
    ->where('id', $id)
    ->firstOrFail();
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        $this->formatInvoice($invoice);

        return response()->json([
            'invoice'   => $invoice,
            'product'   => ['name' => $invoice->product_name],
            'formatted' => [
                'date'         => $invoice->formatted_date,
                'subtotal'     => $invoice->formatted_subtotal,
                'tax_amount'   => $invoice->formatted_tax,
                'discount'     => $invoice->formatted_discount,
                'total_amount' => $invoice->formatted_total,
            ]
        ]);
    }



    
//pdf ko lagi
    public function generatePDF($id)
{
    $user = Auth::user();

    $invoice = Sale::with('customer')
    ->where('user_id', $user->id)
    ->where('id', $id)
    ->firstOrFail();

    if (!$invoice) {
        abort(404, 'Invoice not found');
    }

    $invoice->product_name = $invoice->product_name ?? 'Unknown Product';

    $data = [
        'invoice'                => $invoice,
        'user'                   => $user,
        'date'                   => Carbon::parse($invoice->date)->format('F d, Y'),
        'formatted_date'         => Carbon::parse($invoice->date)->format('F d, Y h:i A'),
        'subtotal_formatted'     => number_format($invoice->subtotal, 2),
        'tax_amount_formatted'   => number_format($invoice->tax_amount, 2),
        'discount_formatted'     => number_format($invoice->discount, 2),
        'total_amount_formatted' => number_format($invoice->total_amount, 2),
    ];

    $pdf = Pdf::loadView('user.invoices.print', $data);

    return $pdf->download('invoice-' . $invoice->id . '.pdf');
}

  
    public function print($id)
    {
        $user = Auth::user();

        $invoice = Sale::with('customer')
    ->where('user_id', $user->id)
    ->where('id', $id)
    ->firstOrFail();

        if (!$invoice) {
            abort(404, 'Invoice not found');
        }

        $this->formatInvoice($invoice);

        return view('user.invoices.print', [
            'invoice'                => $invoice,
            'user'                   => $user,
            'date'                   => Carbon::parse($invoice->date)->format('F d, Y'),
            'formatted_date'         => Carbon::parse($invoice->date)->format('F d, Y h:i A'),
            'subtotal_formatted'     => $invoice->formatted_subtotal,
            'tax_amount_formatted'   => $invoice->formatted_tax,
            'discount_formatted'     => $invoice->formatted_discount,
            'total_amount_formatted' => $invoice->formatted_total,
        ]);
    }

  
    public function downloadAll(Request $request)
    {
        $user = Auth::user();
        $months = $request->months ?? 3;

        $invoices = $this->fetchInvoices($user->id, $months);

        $pdf = Pdf::loadView('user.invoices.exportall', [
            'invoices'       => $invoices,
            'user'           => $user,
            'months'         => $months,
            'totalRevenue'   => $invoices->sum('total_amount'),
            'totalTax'       => $invoices->sum('tax_amount'),
            'totalDiscount'  => $invoices->sum('discount'),
            'date_range'     => [
                'start' => Carbon::now()->subMonths($months)->format('F d, Y'),
                'end'   => Carbon::now()->format('F d, Y'),
            ]
        ]);

        return $pdf->download('invoices-' . now()->format('Y-m-d') . '.pdf');
    }

 
    private function formatInvoice(&$invoice)
    {
        $invoice->product_name = $invoice->product_name ?? 'Unknown Product';

        $invoice->formatted_date      = Carbon::parse($invoice->date)->format('Y-m-d');
        $invoice->formatted_subtotal  = number_format($invoice->subtotal, 2);
        $invoice->formatted_tax       = number_format($invoice->tax_amount, 2);
        $invoice->formatted_discount  = number_format($invoice->discount, 2);
        $invoice->formatted_total     = number_format($invoice->total_amount, 2);

  $invoice->customer_name = Str::limit($invoice->customer->name ?? 'N/A', 20);
    }
}