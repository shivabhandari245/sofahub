<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleItemController extends Controller
{
    // Show all sale items
    public function index()
    {
        $saleItems = SaleItem::with(['sale', 'product'])->latest()->get();
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

        if ($saleItem->is_returned) {
            abort(400, 'This item has already been returned.');
        }

        $product = $saleItem->product;
        $product->quantity += $saleItem->quantity;
        $product->save();

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

        $remainingItems = SaleItem::where('sale_id', $sale->id)
            ->where('is_returned', false)
            ->get();

        $subtotal = $remainingItems->sum('subtotal');
        $profit   = $remainingItems->sum('profit');

        $taxAmount   = round($subtotal * ($sale->tax_rate / 100), 2);
        $totalAmount = round($subtotal + $taxAmount - $sale->discount, 2);

        $sale->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmount,
            'total_amount' => max($totalAmount, 0),
            'profit'       => $profit,
            'status'       => $remainingItems->count() === 0
                                ? 'returned'
                                : 'partially_returned',
        ]);
    });

    return redirect()->back()->with('success', 'Product returned successfully!');
}


}
