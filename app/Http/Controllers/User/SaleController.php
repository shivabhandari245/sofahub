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
use Yajra\DataTables\DataTables;
class SaleController extends Controller
{

public function index(Request $request)
{
    if ($request->ajax()) {
        $query = Sale::with(['user', 'customer'])
                     ->withCount('items')
                     ->orderBy('created_at', 'desc');

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return DataTables::of($query)
            // Global search for DataTables search box
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('id', 'like', "%{$search}%")
                          ->orWhereHas('customer', function ($q2) use ($search) {
                              $q2->where('name', 'like', "%{$search}%");
                          });
                    });
                }
            })
            ->addColumn('customer', fn($sale) => $sale->customer->name ?? 'Walk-in')
            ->addColumn('user', fn($sale) => $sale->user->name ?? 'N/A')
           ->addColumn('status', function($sale) {
    switch($sale->status) {
        case 'returned':
            return '<span class="badge bg-danger">Returned</span>';
        case 'partially_returned':
            return '<span class="badge bg-warning">Partially Returned</span>';
        case 'completed':
        default:
            return '<span class="badge bg-success">Completed</span>';
    }
})


            ->editColumn('date', fn($sale) => $sale->date->format('d M Y h:i A'))
            ->editColumn('subtotal', fn($sale)=>$sale->subtotal)
            ->editColumn('discount', fn($sale) => $sale->discount, 2)
            ->editColumn('afterdiscount', fn($sale) => $sale->afterdiscount)
            ->editColumn('tax_amount', fn($sale) =>$sale->tax_amount)
            ->editColumn('total_amount', fn($sale) =>$sale->total_amount)
         ->editColumn('profit', fn($sale) => $sale->profit)
            ->editColumn('profitafterdiscount', fn($sale) =>$sale->profitafterdiscount ?? $sale->profit)
            ->addColumn('actions', fn($sale) => view('user.sales.action', compact('sale'))->render())
            ->rawColumns(['status','actions'])
            ->make(true);
    }

    return view('user.sales.index');
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
    $columns = [
        0 => 'name',
        1 => 'category',
        2 => 'quality',
        3 => 'cost_per_product',
        4 => 'quantity',
    ];

    $query = ProductModel::where('quantity', '>', 0);

    // Category filter
    if ($request->filled('category_id')) {
        $query->where('category', trim($request->category_id));
    }

    // Global search (DataTables)
    if ($search = $request->input('search.value')) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
        });
    }

    $totalRecords = $query->count();

    // Ordering
    $orderColumnIndex = $request->input('order.0.column', 0);
    $orderDirection   = $request->input('order.0.dir', 'asc');
    $orderColumn      = $columns[$orderColumnIndex] ?? 'name';

    $query->orderBy($orderColumn, $orderDirection);

    // Pagination (DataTables uses start & length)
    $start  = $request->input('start', 0);
    $length = $request->input('length', 10);

    $products = $query->skip($start)->take($length)->get();

    return response()->json([
        'draw'            => intval($request->input('draw')),
        'recordsTotal'    => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $products->map(fn ($product) => [
            'id'               => $product->id,
            'name'             => $product->name,
            'category'         => $product->category,
            'quality'          => $product->quality,
            'cost_per_product' => number_format($product->cost_per_product, 2),
            'quantity'         => $product->quantity,
        ])
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
    // 1️⃣ Validate the request
    $validated = $request->validate([
        'customer_id'      => 'required|exists:customers,id',
        'cartItems'        => 'required|json',
        'tax_rate'         => 'nullable|numeric|min:0|max:100',
        'discount'         => 'nullable|numeric|min:0',
        'payment_method'   => 'nullable|',           // Array of methods    
        'payment_remarks'  => 'nullable|string|max:500',
        'payment_status'   => 'required|in:paid,unpaid,partially_paid',
    ]);

    $paymentMethod  = $validated['payment_method'] ?? []; // Already array
    $paymentRemarks = $validated['payment_remarks'] ?? null;
    $paymentStatus  = $validated['payment_status'];

    // 2️⃣ Business rules for payments
    if ($paymentStatus === 'unpaid') {
        $paymentMethod  = [];
        $paymentRemarks = null;
    }

    if ($paymentStatus === 'paid' && empty($paymentMethod)) {
        return back()->withErrors(['payment_method' => 'Payment method is required for paid sales.']);
    }

    $cartItems  = json_decode($validated['cartItems'], true);
    $taxRate    = $validated['tax_rate'] ?? 0;
    $discount   = $validated['discount'] ?? 0;
    $customerId = $validated['customer_id'];

    DB::beginTransaction();

    try {
        // 3️⃣ Create the sale first
        $sale = Sale::create([
            'customer_id'     => $customerId,
            'subtotal'        => 0,
            'afterdiscount'   => 0,
            'tax_rate'        => $taxRate,
            'tax_amount'      => 0,
            'discount'        => $discount,
            'total_amount'    => 0,
            'profit'          => 0,
            'profitafterdiscount' => 0,
            'user_id'         => Auth::id(),
            'status'          => 'completed',

            // Payment info
            'payment_status'  => $paymentStatus,
            'payment_method'  => $paymentMethod,   // store array directly
            'payment_remarks' => $paymentRemarks,
        ]);

        $subtotal    = 0;
        $totalProfit = 0;

        // 4️⃣ Process cart items
        foreach ($cartItems as $item) {
            $product = ProductModel::findOrFail($item['product_id']);

            if ($product->quantity < $item['quantity']) {
                throw new \Exception("Insufficient stock for product {$product->name}");
            }

            $product->decrement('quantity', $item['quantity']);

            $itemSubtotal = $item['unit_price'] * $item['quantity'];
            $itemProfit   = ($item['unit_price'] - $product->cost_per_product) * $item['quantity'];

            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $product->id,
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'cost_price' => $product->cost_per_product,
                'subtotal'   => $itemSubtotal,
                'profit'     => $itemProfit,
            ]);

            $subtotal    += $itemSubtotal;
            $totalProfit += $itemProfit;
        }

        // 5️⃣ Calculate totals
        $afterDiscount       = max(0, $subtotal - $discount);
        $taxAmount           = round($afterDiscount * ($taxRate / 100), 2);
        $totalAmount         = round($afterDiscount + $taxAmount, 2);
        $profitAfterDiscount = max(0, $totalProfit - $discount);

        // 6️⃣ Update sale totals
        $sale->update([
            'subtotal'           => $subtotal,
            'afterdiscount'      => $afterDiscount,
            'tax_amount'         => $taxAmount,
            'total_amount'       => $totalAmount,
            'profit'             => $totalProfit,
            'profitafterdiscount'=> $profitAfterDiscount,
        ]);

        DB::commit();

        return redirect()
            ->route('user.sales.show', $sale->id)
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