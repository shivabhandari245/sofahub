<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RawMaterialModel;
use App\Models\RawMaterialCategoryModel;
use App\Models\SupplierModel;
use App\Models\UnitModel;
use App\Models\MaterialHistoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Added this import

class RawMaterialController extends Controller
{
    public function index()
    {
        try {
            $materials = RawMaterialModel::with(['category', 'supplier', 'unit'])
                ->orderBy('created_at', 'desc')
                ->get();
            $lowStockCount = RawMaterialModel::where('quantity','<',50)->count();
            $materialCategories = RawMaterialCategoryModel::orderBy('name', 'asc')->get();
            $suppliers = SupplierModel::orderBy('name', 'asc')->get();
            $units = UnitModel::orderBy('name', 'asc')->get();

            return view('admin.rawmaterial.rawmaterials', compact(
                'materials',
                'materialCategories',
                'suppliers',
                'units',
                'lowStockCount'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading raw materials page: ' . $e->getMessage());
            return back()->with('error', 'Failed to load raw materials page.');
        }
    }

    public function insert(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:rawmaterialcategories,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'unit_id' => 'required|exists:units,id',
                'quantity' => 'required|integer|min:0',
                'unit_cost' => 'required|numeric|min:0',
                'storage_location' => 'nullable|string|max:255',
                'min_stock_level' => 'nullable|integer|min:0',
                'max_stock_level' => 'nullable|integer|min:' . ($request->min_stock_level ?? 0),
            ]);

            $validated['total_cost'] = $validated['quantity'] * $validated['unit_cost'];
            $validated['created_by'] = Auth::id();

            $material = RawMaterialModel::create($validated);

            // Save initial history
            MaterialHistoryModel::create([
                'raw_material_id' => $material->id,
                'old_quantity' => 0,
                'quantity_change' => $validated['quantity'],
                'new_quantity' => $validated['quantity'],
                'old_unit_cost' => 0,
                'unit_cost' => $validated['unit_cost'],
                'total_cost_change' => $validated['total_cost'],
                'type' => 'initial_stock',
                'notes' => 'Initial stock entry',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Material added successfully!',
                'data' => $material
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add raw material: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to add material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $material = RawMaterialModel::findOrFail($id);
            $oldQty = $material->quantity;
            $oldUnitCost = $material->unit_cost;

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:rawmaterialcategories,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'unit_id' => 'required|exists:units,id',
                'quantity' => 'required|integer|min:0',
                'unit_cost' => 'required|numeric|min:0',
                'storage_location' => 'nullable|string|max:255',
                'min_stock_level' => 'nullable|integer|min:0',
                'max_stock_level' => 'nullable|integer|min:' . ($request->min_stock_level ?? 0),
            ]);

            $validated['total_cost'] = $validated['quantity'] * $validated['unit_cost'];
            
            $change = $validated['quantity'] - $oldQty;
            $type = $change >= 0 ? 'restocked' : 'used';

            $material->update($validated);

            // Save history
            MaterialHistoryModel::create([
                'raw_material_id' => $material->id,
                'old_quantity' => $oldQty,
                'quantity_change' => abs($change),
                'new_quantity' => $validated['quantity'],
                'old_unit_cost' => $oldUnitCost,
                'unit_cost' => $validated['unit_cost'],
                'total_cost_change' => $validated['total_cost'] - ($oldQty * $oldUnitCost),
                'type' => $type,
                'notes' => $request->notes ?? 'Material updated',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Material updated successfully!',
                'data' => $material
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update raw material: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $material = RawMaterialModel::findOrFail($id);
            $material->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Raw material deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete raw material: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete material: ' . $e->getMessage()
            ], 500);
        }
    }

    public function view($id)
    {
        $material = RawMaterialModel::with(['category', 'supplier', 'unit'])->findOrFail($id);

        $history = MaterialHistoryModel::where('raw_material_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->with('user')
                    ->get();

        // Calculate statistics
        $totalRestocked = $history->where('type', 'restocked')->sum('quantity_change');
        $totalUsed = $history->where('type', 'used')->sum('quantity_change');
        $totalTransactions = $history->count();

        return view('admin.rawmaterial.historymaterial', compact(
            'material', 
            'history',
            'totalRestocked',
            'totalUsed',
            'totalTransactions'
        ));
    }

    public function restock(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $material = RawMaterialModel::findOrFail($id);
            $oldQty = $material->quantity;
            $oldUnitCost = $material->unit_cost;

            $validated = $request->validate([
                'restock_quantity' => 'required|integer|min:1',
                'unit_cost' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:500',
            ]);

            $restockQty = $validated['restock_quantity'];
            $newQty = $oldQty + $restockQty;
            $newUnitCost = $validated['unit_cost'];

            $material->update([
                'quantity' => $newQty,
                'unit_cost' => $newUnitCost,
                'total_cost' => $newQty * $newUnitCost
            ]);

            MaterialHistoryModel::create([
                'raw_material_id' => $material->id,
                'old_quantity' => $oldQty,
                'quantity_change' => $restockQty,
                'new_quantity' => $newQty,
                'old_unit_cost' => $oldUnitCost,
                'unit_cost' => $newUnitCost,
                'total_cost_change' => ($newQty * $newUnitCost) - ($oldQty * $oldUnitCost),
                'type' => 'restocked',
                'notes' => $validated['notes'] ?? 'Restock inventory',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Material restocked successfully!',
                'data' => $material
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to restock raw material: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to restock material: ' . $e->getMessage()
            ], 500);
        }
    }

public function useStock(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $material = RawMaterialModel::findOrFail($id);
        $oldQty = $material->quantity;

        $validated = $request->validate([
            'use_quantity' => 'required|integer|min:1|max:' . $oldQty,
            'notes' => 'required|string|max:500',
        ]);

        $useQty = $validated['use_quantity'];
        $newQty = $oldQty - $useQty;

        // Update stock
        $material->update([
            'quantity' => $newQty,
            'total_cost' => $newQty * $material->unit_cost
        ]);

        // Create a UsedMaterialModel entry (so observers can recalc batch costs if needed)
        \App\Models\UsedMaterialModel::create([
            'raw_material_id' => $material->id,
            'batchproduct_id' => null, // leave null if this is not linked to a batch yet
            'quantity_used' => $useQty,
            'unit_cost' => $material->unit_cost,
            'total_cost' => $useQty * $material->unit_cost,
            'notes' => $validated['notes'],
            'created_by' => Auth::id(),
        ]);

        // Save history
        \App\Models\MaterialHistoryModel::create([
            'raw_material_id' => $material->id,
            'old_quantity' => $oldQty,
            'quantity_change' => $useQty,
            'new_quantity' => $newQty,
            'unit_cost' => $material->unit_cost,
            'total_cost_change' => -($useQty * $material->unit_cost),
            'type' => 'used',
            'notes' => $validated['notes'],
            'created_by' => Auth::id(),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Material stock updated successfully!',
            'data' => $material
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to use material stock: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to update stock: ' . $e->getMessage()
        ], 500);
    }
}

}