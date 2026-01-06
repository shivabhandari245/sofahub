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
    public function index()
    {
        $categories = UserCategory::pluck('name');
        $suppliers = UserSupplier::pluck('name');
        return view('user.userproducts.purchase', compact('categories','suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name'=>'required|string|max:255',
            'category'=>'required|string|max:255',
            'supplier_name'=>'required|string|max:255',
            'supplier_contact'=>'nullable|string|max:255',
            'quantity'=>'required|integer|min:1',
            'unit_cost'=>'required|numeric|min:0',
            'quality'=>'nullable|string|max:255',
            'delivery_date'=>'nullable|date',
        ]);

        DB::transaction(function() use ($request) {
            $category = UserCategory::firstOrCreate(['name'=>$request->category]);
            $supplier = UserSupplier::firstOrCreate(
                ['name'=>$request->supplier_name],
                ['contact'=>$request->supplier_contact]
            );

            $total = $request->quantity * $request->unit_cost;

            $purchase = PurchaseModel::create([
                'user_id'=>Auth::id(),
                'product_name'=>$request->product_name,
                'category'=>$category->name,
                'supplier_name'=>$supplier->name,
                'supplier_contact'=>$supplier->contact,
                'quantity'=>$request->quantity,
                'unit_cost'=>$request->unit_cost,
                'totalcost'=>$total,
                'quality'=>$request->quality,
                'delivery_date'=>$request->delivery_date,
                'status'=>'Purchased',
            ]);

            ProductModel::create([
                'user_id'=>Auth::id(),
                'name'=>$request->product_name,
                'category'=>$category->name,
                'quality'=>$request->quality,
                'quantity'=>$request->quantity,
                'cost_per_product'=>$request->unit_cost,
                'total_cost'=>$total,
                'source'=>'purchase',
            ]);
        });

        return response()->json(['message'=>'Purchase saved successfully!']);
    }

    public function latestPurchases(Request $request)
    {
        $columns = ['id','product_name','category','supplier_name','quantity','unit_cost','totalcost','quality','delivery_date','status'];
        $totalData = PurchaseModel::where('user_id',Auth::id())->count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumn = $columns[$request->input('order.0.column',0)];
        $orderDir = $request->input('order.0.dir','desc');
        $search = $request->input('search.value');

        $query = PurchaseModel::where('user_id',Auth::id());

        if(!empty($search)){
            $query->where(function($q) use ($search){
                $q->where('product_name','LIKE',"%{$search}%")
                  ->orWhere('category','LIKE',"%{$search}%")
                  ->orWhere('supplier_name','LIKE',"%{$search}%")
                  ->orWhere('status','LIKE',"%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        $purchases = $query->offset($start)->limit($limit)->orderBy($orderColumn,$orderDir)->get();

        $data = [];
        foreach($purchases as $index=>$purchase){
            $data[] = [
                'DT_RowIndex'=>$start+$index+1,
                'id'=>$purchase->id,
                'product_name'=>$purchase->product_name,
                'category'=>$purchase->category,
                'supplier_name'=>$purchase->supplier_name,
                'quantity'=>$purchase->quantity,
                'unit_cost'=>$purchase->unit_cost,
                'total'=>$purchase->quantity * $purchase->unit_cost,
                'quality'=>$purchase->quality,
                'delivery_date'=>$purchase->delivery_date ? Carbon::parse($purchase->delivery_date)->format('Y-m-d') : '',
                'status'=>$purchase->status,
            ];
        }

        return response()->json([
            'draw'=>intval($request->input('draw')),
            'recordsTotal'=>$totalData,
            'recordsFiltered'=>$totalFiltered,
            'data'=>$data
        ]);
    }

    public function edit($id)
    {
        $purchase = PurchaseModel::where('user_id',Auth::id())->findOrFail($id);
        return response()->json($purchase);
    }

    public function update(Request $request,$id)
    {
        $purchase = PurchaseModel::where('user_id',Auth::id())->findOrFail($id);
        $request->validate([
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
            'product_name'=>$request->product_name,
            'category'=>$request->category,
            'supplier_name'=>$request->supplier_name,
            'supplier_contact'=>$request->supplier_contact,
            'quantity'=>$request->quantity,
            'unit_cost'=>$request->unit_cost,
            'totalcost'=>$request->quantity*$request->unit_cost,
            'quality'=>$request->quality,
            'delivery_date'=>$request->delivery_date,
            'status'=>$request->status,
        ]);

        return response()->json(['message'=>'Purchase updated successfully!']);
    }

    public function destroy($id)
    {
        $purchase = PurchaseModel::where('user_id',Auth::id())->findOrFail($id);
        $purchase->delete();
        return response()->json(['message'=>'Purchase deleted successfully!']);
    }
}
