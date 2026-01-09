<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class SaleItemController extends Controller
{
    // Show all sale items
    public function index()
    {
         $saleItems = SaleItem::with(['sale', 'product'])
        ->whereHas('sale', function($query) {
            $query->where('user_id', Auth::id());
         
        })
        ->latest()
        ->get();
        return view('user.sales.saleitem', compact('saleItems'));
    }

    // Return a product
public function returnItem(Request $request, $id)
{
    $request->validate([
        'return_reason' => 'required|string|max:255',
    ]);

    DB::transaction(function () use ($id, $request) {

        $saleItem = SaleItem::with(['product', 'sale'])
            ->lockForUpdate()
            ->findOrFail($id);

        if ($saleItem->sale->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to return this item.');
        }

        // 🔁 Restore product stock
        $product = $saleItem->product;
        $product->quantity += $saleItem->quantity;
        $product->save();

        // 🔴 Mark sale item as returned and zero it
        $saleItem->update([
            'is_returned'       => true,
            'returned_quantity' => $saleItem->quantity,
            'returned_at'       => now(),
            'return_reason'     => $request->return_reason,
            'subtotal'          => 0,
            'profit'            => 0,
            'status'            => 'Returned',
        ]);

        $sale = $saleItem->sale;

        // 🔍 Get remaining (non-returned) items
        $remainingItems = SaleItem::where('sale_id', $sale->id)
            ->where('is_returned', false)
            ->get();

        // ✅ CASE 1: ALL ITEMS RETURNED → ZERO EVERYTHING
        if ($remainingItems->count() === 0) {

            $sale->update([
                'subtotal'              => 0,
                'afterdiscount'         => 0,
                'discount'              => 0,
                'tax_amount'            => 0,
                'total_amount'          => 0,
                'profit'                => 0,
                'profitafterdiscount'   => 0,

                'payment_status'        => 'unpaid',
                'payment_method'        => null,
                'payment_remarks'       => 'Sale fully returned',

                'status'                => 'returned',
            ]);

        } else {
            //  PARTIAL RETURN → RECALCULATE
            $subtotal = $remainingItems->sum('subtotal');
            $profit   = $remainingItems->sum('profit');

            $afterDiscount = max(0, $subtotal - $sale->discount);
            $taxAmount     = round($afterDiscount * ($sale->tax_rate / 100), 2);
            $totalAmount   = round($afterDiscount + $taxAmount, 2);
            $profitAfter   = max(0, $profit - $sale->discount);

            $sale->update([
                'subtotal'              => $subtotal,
                'afterdiscount'         => $afterDiscount,
                'tax_amount'            => $taxAmount,
                'total_amount'          => $totalAmount,
                'profit'                => $profit,
                'profitafterdiscount'   => $profitAfter,
                'status'                => 'partially_returned',
            ]);
        }
    });

    return redirect()->back()->with('success', 'Product returned successfully!');
}


}
