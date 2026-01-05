<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseModel;
use App\Models\ProductModel;
use App\Models\UserCategory;
use App\Models\UserSupplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserPurchaseController extends Controller
{
    // Show purchase form
    public function index()
    {
        $categories = UserCategory::pluck('name');
        $suppliers = UserSupplier::pluck('name');

        return view('user.userproducts.purchase', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'supplier' => 'required|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'quality' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {

            // Category
            $category = UserCategory::firstOrCreate([
                'name' => $request->category
            ]);

            // Supplier
            $supplier = UserSupplier::firstOrCreate(
                ['name' => $request->supplier],
                ['contact' => $request->supplier_contact]
            );

            // Calculate total cost
            $totalCost = $request->quantity * $request->unit_cost;

            // Create purchase record
            $purchase = PurchaseModel::Create([
                'user_id' => Auth::id(),
                'product_name' => $request->product_name,
                'category' => $category->name,
                'supplier_name' => $supplier->name,
                'supplier_contact' => $supplier->contact,
                'quantity' => $request->quantity,
                'unit_cost' => $request->unit_cost,
                'totalcost' => $totalCost,
                'quality' => $request->quality,
                'delivery_date' => $request->delivery_date,
                'status' => 'Purchased',
            ]);

            // Add product to inventory
            ProductModel::Create([
                'name' => $request->product_name,
                'category' => $category->name,
                'quality' => $request->quality,
                'user_id' => Auth::id(),
                'quantity' => $request->quantity,
                'cost_per_product' => $request->unit_cost,
                'total_cost' => $totalCost,
                'source' => 'purchase',
                'dispatch_id' => null,
            ]);
        });

        return response()->json([
            'message' => 'Product purchased and added to inventory successfully!'
        ]);
    }

   public function latestPurchases(Request $request)
{
    $purchases = PurchaseModel::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->paginate(5);

    $formatted = $purchases->getCollection()->map(function ($purchase) {
        return [
            'id' => $purchase->id,
            'product_name' => $purchase->product_name,
            'category' => $purchase->category,
            'quantity' => $purchase->quantity,
            'unit_cost' => $purchase->unit_cost,
            'quality' => $purchase->quality,
            'delivery_date' => $purchase->delivery_date ? Carbon::parse($purchase->delivery_date)->format('Y-m-d') : null,
            'status' => $purchase->status,
            'supplier_name' => $purchase->supplier_name,
        ];
    });

    $purchases->setCollection($formatted);

    return response()->json([
        'data' => $purchases->items(),
        'current_page' => $purchases->currentPage(),
        'last_page' => $purchases->lastPage(),
    ]);
}

}
