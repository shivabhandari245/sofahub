<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductModel;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\UserCategory;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{

public function index(Request $request)
{
    $query = Sale::with(['user', 'customer'])->orderBy('created_at', 'desc');
    
  
    if ($request->has('search') && $request->search) {
        $query->where(function ($query) use ($request) {
            $query->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function ($query) use ($request) {
                      $query->where('name', 'like', '%' . $request->search . '%');
                  });
        });
    }

   
    if ($request->has('date_from') && $request->date_from) {
        $query->where('date', '>=', $request->date_from);
    }

 
    if ($request->has('date_to') && $request->date_to) {
        $query->where('date', '<=', $request->date_to);
    }

  
    $sales = $query->paginate(20);
    
  
    $categories = UserCategory::orderBy('name')->get();

    return view('user.sales.index', compact('sales', 'categories'));
}




    public function create()
    {
        
        $products = ProductModel::where('quantity', '>', 0)
            ->orderBy('name')
            ->get();
              $categories = UserCategory::orderBy('name')->get();      
        return view('user.sales.create', compact('products', 'categories'));
    }

public function ajaxList(Request $request)
{
    $query = ProductModel::where('quantity', '>', 0);

    if ($request->filled('category_id')) {
        $query->where('category', trim($request->category_id));
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
        });
    }

    $products = $query->orderBy('name')->paginate(10);

    return response()->json([
        'data' => $products->map(fn($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'quantity' => $product->quantity,
            'cost_per_product' => $product->cost_per_product,
            'category' => $product->category,
            'quality' => $product->quality,
        ]),
        'current_page' => $products->currentPage(),
        'last_page' => $products->lastPage(),
        'total' => $products->total(),
    ]);
}


public function getCustomers(Request $request)
{
    $query = \App\Models\Customer::query();

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
    }

    $customers = $query->limit(10)->get();

    return response()->json($customers);
}

public function store(Request $request)
{
   
    $validated = $request->validate([
    'customer_id'     => 'required|exists:customers,id',
    'cartItems'       => 'required|json',
    'tax_rate'        => 'nullable|numeric|min:0|max:100',
    'discount'        => 'nullable|numeric|min:0',
    'payment_method'  => 'nullable|string', // added
    'payment_remarks' => 'nullable|string', // added
]);

$paymentMethod  = $validated['payment_method'] ?? null; 
$paymentRemarks = $validated['payment_remarks'] ?? null; 

    $cartItems = json_decode($validated['cartItems'], true);
    $taxRate   = $validated['tax_rate'] ?? 0;   // default 0% if not provided
    $discount  = $validated['discount'] ?? 0;   // default 0 if not provided
    $customerId = $validated['customer_id'];

    DB::beginTransaction();
    try {
        // First, create the sale with placeholders
        $sale = Sale::create([
            'customer_id' => $customerId,
            'subtotal'    => 0,
            'tax_rate'    => $taxRate,
            'tax_amount'  => 0,
            'discount'    => $discount,
            'total_amount'=> 0,
            'profit'      => 0,
            'user_id'     => auth::id(),
            'status'      => 'completed',
            'payment_method' => $paymentMethod,
    'payment_remarks' => $paymentRemarks,
        ]);

        $subtotal   = 0;
        $totalProfit= 0;

       
        foreach ($cartItems as $item) {
            $product = ProductModel::findOrFail($item['product_id']);

            if ($product->quantity < $item['quantity']) {
                throw new \Exception("Insufficient stock for product {$product->name}");
            }

            $product->decrement('quantity', $item['quantity']);

            $itemSubtotal = $item['unit_price'] * $item['quantity'];
            $itemProfit   = ($item['unit_price'] - $product->cost_per_product) * $item['quantity'];

            SaleItem::create([
                'sale_id'     => $sale->id,
                'product_id'  => $product->id,
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'cost_price'  => $product->cost_per_product,
                'subtotal'    => $itemSubtotal,
                'profit'      => $itemProfit,
            ]);

            $subtotal    += $itemSubtotal;

            $totalProfit += $itemProfit;
        }

      $afterDiscount = max(0, $subtotal - $discount); // never negative
$taxAmount = round($afterDiscount * ($taxRate / 100), 2);
$totalAmount = round($afterDiscount + $taxAmount, 2);


        $sale->update([
    'subtotal'      => $subtotal,
    'afterdiscount' => $afterDiscount,
    'tax_rate'      => $taxRate,
    'tax_amount'    => $taxAmount,
    'total_amount'  => $totalAmount,
    'profit'        => $totalProfit,

]);

        DB::commit();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sale processed successfully.',
                'sale_id' => $sale->id,
            ]);
        }

        return redirect()->route('user.sales.show', $sale->id)
                         ->with('success', 'Sale processed successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Failed to process sale: ' . $e->getMessage(),
        ], 400);
    }

}


public function show(Sale $sale)
{
    $sale->load('items.product', 'customer', 'user');
    return view('user.sales.show', compact('sale'));
}

public function print(Sale $sale)
{
   
    $sale->load('items.product', 'customer', 'user');

  
    return view('user.sales.print', compact('sale'));
}




}