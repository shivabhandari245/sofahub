<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Normal page load
        if (!$request->ajax()) {
            return view('admin.product.product');
        }

        $query = ProductModel::query();

        // Search
        if ($request->search_value) {
            $query->where('name', 'like', '%' . $request->search_value . '%');
        }

        // Source filter
        if ($request->source && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        // Status filter
        if ($request->status && $request->status !== 'all') {
            if ($request->status === 'Available') {
                $query->where('quantity', '>=', 5);
            } elseif ($request->status === 'Low') {
                $query->whereBetween('quantity', [1, 4]);
            } elseif ($request->status === 'Out of Stock') {
                $query->where('quantity', 0);
            }
        }

        $recordsTotal = ProductModel::count();
        $recordsFiltered = $query->count();

        $products = $query
            ->orderBy('created_at', 'desc')
            ->skip($request->start)
            ->take($request->length)
            ->get();

        $data = $products->map(function ($p) {
            return [
                'name' => $p->name,
                'category' => $p->category ?? '-',
                'quality' => $p->quality ?? '-',
                'quantity' => $p->quantity,
                'cost_per_product' => $p->cost_per_product,
                'total_cost' => $p->total_cost,
                'showroom' => $p->user->name,
                'source' => $p->source,
                'status' => $p->quantity == 0
                    ? '<span class="badge bg-danger">Out of Stock</span>'
                    : ($p->quantity < 5
                        ? '<span class="badge bg-warning">Low Stock</span>'
                        : '<span class="badge bg-success">Available</span>')
            ];
        });

        $stats = [
    'total' => ProductModel::count(),
    'available' => ProductModel::where('quantity', '>=', 5)->count(),
    'low' => ProductModel::whereBetween('quantity', [1, 4])->count(),
    'out' => ProductModel::where('quantity', 0)->count(),
];

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'stats' => $stats
        ]);

    }
}
