<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseModel;
use App\Models\ProductModel;
use App\Models\UserCategory;
use App\Models\UserSupplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserPurchaseController extends Controller
{
    // List purchase page
    public function index()
    {
        $categories = UserCategory::pluck('name');
        $suppliers = UserSupplier::pluck('name');
        return view('user.userproducts.purchase', compact('categories','suppliers'));
    }

    // Store new purchase
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name'=>'required|string|max:255',
            'category'=>'required|string|max:255',
            'supplier_name'=>'required|string|max:255',
            'supplier_contact'=>'nullable|string|max:255',
            'quantity'=>'required|integer|min:1',
            'unit_cost'=>'required|numeric|min:0',
            'quality'=>'nullable|string|max:255',
            'delivery_date'=>'nullable|date',
        ]);

        DB::transaction(function() use ($validated) {
            $category = UserCategory::firstOrCreate(['name' => $validated['category']]);
            $supplier = UserSupplier::firstOrCreate(
                ['name' => $validated['supplier_name']],
                ['contact' => $validated['supplier_contact'] ?? null]
            );

            $total = $validated['quantity'] * $validated['unit_cost'];

            $purchase = PurchaseModel::create([
                'user_id' => Auth::id(),
                'product_name' => $validated['product_name'],
                'category' => $category->name,
                'supplier_name' => $supplier->name,
                'supplier_contact' => $supplier->contact,
                'quantity' => $validated['quantity'],
                'unit_cost' => $validated['unit_cost'],
                'totalcost' => $total,
                'quality' => $validated['quality'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'status' => 'Purchased',
            ]);

            ProductModel::create([
                'user_id' => Auth::id(),
                'name' => $validated['product_name'],
                'category' => $category->name,
                'quality' => $validated['quality'] ?? null,
                'quantity' => $validated['quantity'],
                'cost_per_product' => $validated['unit_cost'],
                'total_cost' => $total,
                'source' => 'purchase',
            ]);
        });

        return response()->json(['message'=>'Purchase saved successfully!']);
    }

    // Fetch latest purchases with search & pagination
    public function latestPurchases(Request $request)
    {
        $columns = ['id','product_name','category','supplier_name','quantity','unit_cost','totalcost','quality','delivery_date','status'];
        $query = PurchaseModel::where('user_id', Auth::id());

        if ($search = $request->input('search.value')) {
            $query->where(function($q) use ($search) {
                $q->where('product_name','LIKE',"%{$search}%")
                  ->orWhere('category','LIKE',"%{$search}%")
                  ->orWhere('supplier_name','LIKE',"%{$search}%")
                  ->orWhere('status','LIKE',"%{$search}%");
            });
        }

        $totalData = PurchaseModel::where('user_id',Auth::id())->count();
        $totalFiltered = $query->count();

        $purchases = $query
            ->offset($request->input('start',0))
            ->limit($request->input('length',10))
            ->orderBy($columns[$request->input('order.0.column',0)], $request->input('order.0.dir','desc'))
            ->get();

        $data = $purchases->map(function($purchase, $index) use ($request) {
            return [
                'DT_RowIndex' => $request->input('start',0) + $index + 1,
                'id' => $purchase->id,
                'product_name' => $purchase->product_name,
                'category' => $purchase->category,
                'supplier_name' => $purchase->supplier_name,
                'quantity' => $purchase->quantity,
                'unit_cost' => $purchase->unit_cost,
                'total' => $purchase->quantity * $purchase->unit_cost,
                'quality' => $purchase->quality,
                'delivery_date' => $purchase->delivery_date ? Carbon::parse($purchase->delivery_date)->format('Y-m-d') : '',
                'status' => $purchase->status,
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    // Route model binding automatically fetches the purchase and ensures existence
    public function edit(PurchaseModel $purchase)
    {
        $this->authorizePurchase($purchase);
        return response()->json($purchase);
    }

    public function update(Request $request, PurchaseModel $purchase)
    {
        $this->authorizePurchase($purchase);

        $validated = $request->validate([
            'product_name'=>'required|string|max:255',
            'category'=>'required|string|max:255',
            'supplier_name'=>'required|string|max:255',
            'supplier_contact'=>'nullable|string|max:255',
            'quantity'=>'required|integer|min:1',
            'unit_cost'=>'required|numeric|min:0',
            'quality'=>'nullable|string|max:255',
            'delivery_date'=>'nullable|date',
            'status'=>'required|string',
        ]);

        $purchase->update([
            'product_name' => $validated['product_name'],
            'category' => $validated['category'],
            'supplier_name' => $validated['supplier_name'],
            'supplier_contact' => $validated['supplier_contact'] ?? null,
            'quantity' => $validated['quantity'],
            'unit_cost' => $validated['unit_cost'],
            'totalcost' => $validated['quantity'] * $validated['unit_cost'],
            'quality' => $validated['quality'] ?? null,
            'delivery_date' => $validated['delivery_date'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json(['message'=>'Purchase updated successfully!']);
    }

    public function destroy(PurchaseModel $purchase)
    {
        $this->authorizePurchase($purchase);
        $purchase->delete();
        return response()->json(['message'=>'Purchase deleted successfully!']);
    }

    // Ownership check helper
    private function authorizePurchase(PurchaseModel $purchase)
    {
        if ($purchase->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
