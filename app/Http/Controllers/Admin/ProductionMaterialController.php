<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchProductModel;
use App\Models\BatchCostModel;
use App\Models\RawMaterial;
use App\Models\RawMaterialModel;
use App\Models\UsedMaterialModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductionMaterialController extends Controller
{
    public function index($id)
    {
        $batchproduct = BatchProductModel::findOrFail($id);
        $allocatedMaterials = UsedMaterialModel::where('batchproduct_id', $id)
            ->with('rawMaterial.category')
            ->get();
        $materialTypes = \App\Models\RawMaterialCategoryModel::all();
        return view('admin.production.productionmaterial', compact(
            'batchproduct', 'allocatedMaterials', 'materialTypes'
        ));
    }

    public function getMaterialsByCategory($category_id)
    {
        try {
            $materials = RawMaterialModel::where('category_id', $category_id)
                ->where('status', 'available')
                ->with('supplier', 'category', 'unit')
                ->get();

            return response()->json([
                'success' => true,
                'materials' => $materials
            ]);
        } catch (\Exception $e) {
            Log::error("Material Fetch Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Server Error"
            ], 500);
        }
    }

public function allocateMaterial(Request $request)
{
    $validator = Validator::make($request->all(), [
        'batchproduct_id' => 'required|exists:batch_products,id',
        'material_id' => 'required|exists:rawmaterials,id',
        'quantity_used' => 'required|numeric|min:0.01',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    DB::beginTransaction();
    try {
        $material = RawMaterialModel::findOrFail($request->material_id);
        if ($material->quantity < $request->quantity_used) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $material->quantity . ' ' . ($material->unit->name ?? '')
            ], 400);
        }
        $existingAllocation = UsedMaterialModel::where([
            'batchproduct_id' => $request->batchproduct_id,
            'raw_material_id' => $request->material_id
        ])->first();

        if ($existingAllocation) {
 
            if ($material->quantity < $request->quantity_used) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update. New quantity exceeds available stock.'
                ], 400);
            }

            $existingAllocation->quantity_used = $request->quantity_used; 
            $existingAllocation->total_cost = $material->unit_cost * $request->quantity_used;
            $existingAllocation->save();
            
            $allocated = $existingAllocation;
            $action = 'updated';
        } else {
            // Create new allocation
            $allocated = UsedMaterialModel::create([
                'batchproduct_id' => $request->batchproduct_id,
                'raw_material_id' => $request->material_id,
                'quantity_used' => $request->quantity_used,
                'unit_cost' => $material->unit_cost,
                'total_cost' => $material->unit_cost * $request->quantity_used,
                'status' => 'Allocated',
            ]);
            $action = 'allocated';
        }

        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => "Material $action successfully!",
            'data' => $allocated
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Allocation Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Server error occurred'
        ], 500);
    }
}

    public function deleteAllocation($id)
    {
        DB::beginTransaction();
        try {
            $used = UsedMaterialModel::findOrFail($id);
            $used->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Material removed from allocation'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting allocation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not delete allocation'
            ], 500);
        }
    }

    public function showUsedMaterials($id)
    {
        try {
            $usedMaterials = UsedMaterialModel::where('batchproduct_id', $id)
                ->with(['rawMaterial' => function($q) {
                    $q->with('category');
                }])
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'material_name' => $item->rawMaterial->name ?? 'Unknown',
                        'category_name' => $item->rawMaterial->category->name ?? 'Unknown',
                        'quantity_used' => $item->quantity_used,
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $item->total_cost,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'materials' => $usedMaterials
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Show used materials error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch materials'
            ], 500);
        }
    }

public function checkAllocation($batchId, $materialId)
{
    try {
        $allocation = UsedMaterialModel::where([
            'batchproduct_id' => $batchId,
            'raw_material_id' => $materialId
        ])->first();

        return response()->json([
            'allocated' => !is_null($allocation),
            'data' => $allocation ? [
                'quantity_used' => $allocation->quantity_used,
                'total_cost' => $allocation->total_cost
            ] : null
        ]);
    } catch (\Exception $e) {
        Log::error('Check allocation error: ' . $e->getMessage());
        return response()->json([
            'allocated' => false,
            'data' => null
        ]);
    }
}

    public function confirmBatchProduct(Request $request, $id)
    {


        $batchproduct = BatchProductModel::findOrFail($id);
        $usedMaterials = $batchproduct->usedMaterials;

        if ($usedMaterials->isEmpty()) {
            return redirect()->back()->with('error', 'Cannot confirm batch without allocated materials.');
        }

        $materialsCost = $usedMaterials->sum('total_cost');


        // Update batch product cost per unit
        
        $batchproduct->update([
            'material_cost' => $materialsCost,
        ]);
        return redirect('/admin/production')
            ->with('success', 'Batch confirmed successfully!');
    }
}