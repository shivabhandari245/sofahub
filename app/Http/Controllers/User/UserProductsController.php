<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProductsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ProductModel::where('user_id', $user->id);

        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('quality', 'like', "%{$search}%");
            });
        }

       
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status == 'Available') {
                $query->where('quantity', '>', 5);
            } elseif ($request->status == 'Low') {
                $query->whereBetween('quantity', [1,5]);
            } elseif ($request->status == 'Out of Stock') {
                $query->where('quantity', 0);
            }
        }

       
        if ($request->filled('source') && $request->source !== 'all') {
            if ($request->source == 'admin') {
                $query->where('source', 'like', '%Admin%');
            } elseif ($request->source == 'purchased') {
                $query->where('source', 'like', '%purchase%');
            }
        }

      
        $products = $query->orderBy('created_at', 'desc')->paginate(10);

     
        if ($request->ajax()) {
            return view('user.userproducts.partials.products-table', compact('products'))->render();
        }

        return view('user.userproducts.products', compact('products'));
    }

    // --- Delete product ---
    public function destroy($id)
    {
        $user = Auth::user();
        $product = ProductModel::where('id', $id)->where('user_id', $user->id)->first();

        if(!$product){
            return response()->json(['success' => false, 'message' => 'Product not found or not authorized.']);
        }

        try {
            $product->delete();
            return response()->json(['success' => true, 'message' => 'Product deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete product.']);
        }
    }
}