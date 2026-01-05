<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.product.product');
    }

    public function list()
    {
        $products = ProductModel::with([
            'dispatch.user',
            'productCategory'
        ])->orderBy('created_at', 'desc')->get();

        $data = $products->map(function ($p) {
            return [
                'product_name'     => $p->name,
                'category_name'    => $p->category ?? '-',
                'quality_name'     => $p->quality ?? '-',
                'quantity'         => $p->quantity,
                'cost_per_product' => number_format($p->cost_per_product, 2),
                'total_cost'       => number_format($p->total_cost, 2),

                // ✅ SAFE NULL HANDLING
                'showroom_name'    => $p->user->name ?? 'N/A',

                'source'           => $p->source,
            ];
        });

        return response()->json($data);
    }
}