<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialCategoryModel;
use App\Models\SupplierModel;
use App\Models\RawMaterialModel;
use App\Models\UnitModel;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // CATEGORY LIST
    public function listcategory()
    {
        // include id so delete works
        return response()->json(RawMaterialCategoryModel::select('id', 'name')->get());
    }

    // ADD CATEGORY
    public function addcategory(Request $request)
    {
        $category = RawMaterialCategoryModel::create([
            'name' => $request->name
        ]);

        return response()->json(['success' => true, 'id' => $category->id]);
    }

    // SUPPLIER LIST
    public function listsupplier()
    {
        return response()->json(SupplierModel::select('id', 'name')->get());
    }

    // ADD SUPPLIER
    public function addsupplier(Request $request)
    {
        $supplier = SupplierModel::create([
            'name' => $request->name
        ]);

        return response()->json(['success' => true, 'id' => $supplier->id]);
    }

    // UNIT LIST
    public function listunit()
    {
        return response()->json(UnitModel::select('id', 'name')->get());
    }

    // ADD UNIT
    public function addunit(Request $request)
    {
        $unit = UnitModel::create([
            'name' => $request->name
        ]);

        return response()->json(['success' => true, 'id' => $unit->id]);
    }

    // DELETE CATEGORY
public function deleteCategory($id)
{
    $materialsCount = RawMaterialModel::where('category_id', $id)->count();

    if ($materialsCount > 0) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot delete this category because it has raw materials assigned.'
        ]);
    }

    RawMaterialCategoryModel::find($id)->delete();

    return response()->json(['success' => true, 'message' => 'Category deleted successfully!']);
}



    // DELETE SUPPLIER
    public function deletesupplier($id)
    {
        $supplier = SupplierModel::findOrFail($id);
        $supplier->delete();

        return response()->json(['success' => true]);
    }

    // DELETE UNIT
    public function deleteunit($id)
    {
        $unit = UnitModel::findOrFail($id);
        $unit->delete();

        return response()->json(['success' => true]);
    }
}
