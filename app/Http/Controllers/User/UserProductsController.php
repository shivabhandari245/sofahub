<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UserProductsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $products = ProductModel::where('user_id', Auth::id());

            // Live search
            if ($request->filled('search_value')) {
                $products->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search_value}%")
                      ->orWhere('category', 'like', "%{$request->search_value}%")
                      ->orWhere('quality', 'like', "%{$request->search_value}%");
                });
            }

            // Status filter
            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'Available') {
                    $products->where('quantity', '>', 5);
                } elseif ($request->status === 'Low') {
                    $products->whereBetween('quantity', [1, 5]);
                } elseif ($request->status === 'Out of Stock') {
                    $products->where('quantity', 0);
                }
            }

            // Source filter (FIXED)
            if ($request->filled('source') && $request->source !== 'all') {
                if ($request->source === 'admin') {
                    $products->where('source', 'like', '%Admin%');
                } elseif ($request->source === 'purchased') {
                    $products->where('source', 'like', '%purchase%');
                }
            }

            return DataTables::of($products)
                ->addColumn('showroom', fn () => Auth::user()->name)
                ->addColumn('status', function ($p) {
                    if ($p->quantity == 0) {
                        return '<span class="badge bg-danger">Out of Stock</span>';
                    } elseif ($p->quantity <= 5) {
                        return '<span class="badge bg-warning">Low Stock</span>';
                    }
                    return '<span class="badge bg-success">Available</span>';
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('user.userproducts.products');
    }
}
