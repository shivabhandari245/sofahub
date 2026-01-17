<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\RawMaterial;
use App\Models\UsedMaterialModel;
use App\Models\BatchCostModel;
use App\Models\BatchProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsedMaterialController extends Controller
{
    /**
     * AJAX: Load used materials for batch
     */
    public function index(Request $request)
    {
        $request->validate([
            'batchproduct_id' => 'required|',
        ]);

        $batchproduct = BatchProductModel::findOrFail($request->batchProduct_id);

        $usedMaterials = UsedMaterialModel::where('batchproduct_id', $batchproduct->id)
            ->with('rawMaterial')
            ->get();

        $materials = $usedMaterials->map(function ($item) {
            return [
                'id' => $item->id,
                'material_id' => $item->raw_material_id,
                'material_name' => $item->rawMaterial->name ?? 'Unknown',
                'quantity_used' => $item->quantity_used,
                'unit_cost' => $item->rawMaterial->unit_cost ?? 0,
                'total_cost' => $item->total_cost,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'materials' => $materials,
                'quantity_produced' => $batchproduct->quantity ?? 0,
            ],
        ]);
    }

    /**
     * Show all used materials for batch
     */
    public function showUsedMaterials($id)
    {
        $batchproduct = BatchProductModel::find($id);

        if (!$batchproduct) {
            return response()->json([
                'success' => false,
                'message' => 'Batch not found.',
            ], 404);
        }

        $usedMaterials = UsedMaterialModel::where('batchproduct_id', $id)
            ->with('rawMaterial')
            ->orderBy('id', 'desc')
            ->get();

        $materials = $usedMaterials->map(function ($item) {
            return [
                'id' => $item->id,
                'material_id' => $item->raw_material_id,
                'material_name' => $item->rawMaterial->name ?? 'Unknown',
                'category_name' => $item->rawMaterial->category->name ?? 'Unknown',
                'quantity_used' => $item->quantity_used,
                'unit_cost' => $item->rawMaterial->unit_cost ?? 0,
                'total_cost' => $item->total_cost,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'materials' => $materials,
            ],
        ]);
    }

    /**
     * Delete one used material entry and restore stock
     */
    public function deleteUsedMaterial($id)
    {
        $used = UsedMaterialModel::find($id);

        if (!$used) {
            return response()->json([
                'success' => false,
                'message' => 'Used material not found.',
            ], 404);
        }

        $used->delete();

        return response()->json([
            'success' => true,
            'message' => 'Material removed & stock restored.',
        ]);
    }
}
