<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategoryModel;
use App\Models\ProductQualityModel;
use App\Models\BatchProductModel;
use Illuminate\Http\Request;

class BatchCategoryController extends Controller
{
    public function listProductcategory()
    {
        return ProductCategoryModel::orderBy('id', 'desc')->get();
    }

    public function addproductcategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $Productcategory = ProductCategoryModel::create([
            'name' => $request->name
        ]);

        return response()->json(['success' => true, 'id' => $Productcategory->id]);
    }

    public function listquality()
    {
        return ProductQualityModel::orderBy('id', 'desc')->get();
    }

    public function addquality(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $quality = ProductQualityModel::create([
            'name' => $request->name
        ]);
        
        return response()->json(['success' => true, 'id' => $quality->id]);
    }

    public function listbatchproduct()
    {
        return BatchProductModel::orderBy('id', 'desc')->get();
    }

    public function deletecategory($id)
    {
        $category = ProductCategoryModel::find($id);
        if ($category) {
            $category->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false], 404);
        } 
    }       

    public function deletequality($id)
    {
        $quality = ProductQualityModel::find($id);
        if ($quality) {
            $quality->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false], 404);
        } 
    }

public function addbatchproduct(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'productcategory_id' => 'required|integer|exists:product_categories,id',
        'productquality_id' => 'required|integer|exists:product_quality,id',
    ]);

    $product = BatchProductModel::create([
        'name' => $request->name,
        'productcategory_id' => $request->productcategory_id,
        'productquality_id' => $request->productquality_id,
    ]);

    return response()->json([
        'success' => true,
        'id' => $product->id,
        'name' => $product->name
    ]);
}



    public function destroyproduct($id)
    {
        $product = BatchProductModel::find($id);
        if(!$product){
            return response()->json(['success' => false], 404);
        }

        $product->delete();
        return response()->json(['success' => true]);
    }
}