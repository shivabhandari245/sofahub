<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchProductModel;
use App\Models\BatchModel;
use App\Models\RawMaterialModel;
use App\Models\UsedMaterialModel;
use App\Models\MaterialHistoryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductionMaterialController extends Controller
{
    public function index($id)
    {
        // Cache the batch product for 5 minutes
        $batchproduct = Cache::remember("batchproduct_{$id}", 300, function() use ($id) {
            return BatchProductModel::findOrFail($id);
        });
        
        // Load allocated materials with optimized query
        $allocatedMaterials = UsedMaterialModel::where('batchproduct_id', $id)
            ->with(['rawMaterial' => function($query) {
                $query->select('id', 'name', 'category_id')
                      ->with(['category' => function($q) {
                          $q->select('id', 'name');
                      }]);
            }])
            ->select('id', 'raw_material_id', 'quantity_used', 'unit_cost', 'total_cost')
            ->get();
        
        // Cache material categories for 10 minutes
        $materialTypes = Cache::remember('material_categories', 600, function() {
            return \App\Models\RawMaterialCategoryModel::select('id', 'name')->get();
        });
        
        return view('admin.production.productionmaterial', compact(
            'batchproduct', 'allocatedMaterials', 'materialTypes'
        ));
    }

    public function getMaterialsByCategory($category_id)
    {
        // Cache materials by category for 2 minutes
        $cacheKey = "materials_category_{$category_id}";
        
        $materials = Cache::remember($cacheKey, 120, function() use ($category_id) {
            return RawMaterialModel::where('category_id', $category_id)
                ->where('status', 'available')
                ->with(['supplier' => function($q) {
                    $q->select('id', 'name');
                }, 'category' => function($q) {
                    $q->select('id', 'name');
                }, 'unit' => function($q) {
                    $q->select('id', 'name');
                }])
                ->select('id', 'name', 'supplier_id', 'category_id', 'unit_id', 
                        'quantity', 'unit_cost', 'storage_location')
                ->get();
        });
        
        return response()->json([
            'success' => true,
            'materials' => $materials
        ]);
    }

public function allocateMaterial(Request $request)
{
    Log::info('[CONTROLLER] allocateMaterial called', $request->all());
    
    $validated = $request->validate([
        'batchproduct_id' => 'required|exists:batch_products,id',
        'material_id' => 'required|exists:rawmaterials,id',
        'quantity_used' => 'required|numeric|min:0.01',
    ]);
    
    DB::beginTransaction();
    try {
        // Get material with lock for update
        $material = RawMaterialModel::where('id', $validated['material_id'])
            ->lockForUpdate()
            ->firstOrFail();
        
        Log::info('[CONTROLLER] Material found: ' . $material->name . 
                 ', Current stock: ' . $material->quantity);
        
        // Check for existing allocation
        $existingAllocation = UsedMaterialModel::where([
            'batchproduct_id' => $validated['batchproduct_id'],
            'raw_material_id' => $validated['material_id']
        ])->first();
        
        Log::info('[CONTROLLER] Existing allocation: ' . ($existingAllocation ? 'Yes, Qty: ' . $existingAllocation->quantity_used : 'No'));
        
        // Get pending batches for this product
        $pendingBatches = BatchModel::where('batchproduct_id', $validated['batchproduct_id'])
            ->where('status', 'Pending')
            ->get();
        
        Log::info('[CONTROLLER] Found ' . $pendingBatches->count() . ' pending batches');
        
        // Store original quantity
        $originalQuantity = $existingAllocation ? $existingAllocation->quantity_used : 0;
        $newQuantity = $validated['quantity_used'];
        $diff = $newQuantity - $originalQuantity;
        
        Log::info('[CONTROLLER] Original Qty: ' . $originalQuantity . 
                 ', New Qty: ' . $newQuantity . 
                 ', Diff: ' . $diff);
        
        // Handle stock adjustment based on the scenario
        if ($existingAllocation) {
            // UPDATE SCENARIO
            if ($diff > 0) {
                // Quantity increased - need more stock
                $additionalRequired = 0;
                foreach ($pendingBatches as $batch) {
                    $batchAdditional = $diff * $batch->quantity;
                    $additionalRequired += $batchAdditional;
                    Log::info('[CONTROLLER] Batch #' . $batch->id . ' needs additional: ' . $batchAdditional);
                }
                
                Log::info('[CONTROLLER] Total additional required: ' . $additionalRequired . 
                         ', Available: ' . $material->quantity);
                
                if ($material->quantity < $additionalRequired) {
                    DB::rollBack();
                    Log::error('[CONTROLLER] Insufficient stock for quantity increase');
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for quantity increase. Available: {$material->quantity}, Additional required: {$additionalRequired}"
                    ], 400);
                }
                
                // Deduct additional stock
                foreach ($pendingBatches as $batch) {
                    $qtyToDeduct = $diff * $batch->quantity;
                    $material->decrement('quantity', $qtyToDeduct);
                    
                    Log::info('[CONTROLLER] Deducted ' . $qtyToDeduct . ' for Batch #' . $batch->id);
                    
                    // Record history
                    MaterialHistoryModel::create([
                        'raw_material_id'   => $material->id,
                        'old_quantity'      => $material->quantity + $qtyToDeduct,
                        'quantity_change'   => -$qtyToDeduct,
                        'new_quantity'      => $material->fresh()->quantity,
                        'old_unit_cost'     => $material->unit_cost,
                        'unit_cost'         => $material->unit_cost,
                        'total_cost_change' => $qtyToDeduct * $material->unit_cost,
                        'type'              => 'used',
                        'notes'             => "Material '{$material->name}' quantity increased in recipe for Batch #{$batch->id} (Additional: {$qtyToDeduct})",
                        'created_by'        => auth()->id(),
                    ]);
                }
                
            } elseif ($diff < 0) {
                // Quantity decreased - return excess stock
                $returnQty = abs($diff);
                
                foreach ($pendingBatches as $batch) {
                    $qtyToReturn = $returnQty * $batch->quantity;
                    $material->increment('quantity', $qtyToReturn);
                    
                    Log::info('[CONTROLLER] Returned ' . $qtyToReturn . ' for Batch #' . $batch->id);
                    
                    // Record history
                    MaterialHistoryModel::create([
                        'raw_material_id'   => $material->id,
                        'old_quantity'      => $material->quantity - $qtyToReturn,
                        'quantity_change'   => $qtyToReturn,
                        'new_quantity'      => $material->fresh()->quantity,
                        'old_unit_cost'     => $material->unit_cost,
                        'unit_cost'         => $material->unit_cost,
                        'total_cost_change' => $qtyToReturn * $material->unit_cost,
                        'type'              => 'adjusted',
                        'notes'             => "Material '{$material->name}' quantity decreased in recipe for Batch #{$batch->id} (Returned: {$qtyToReturn})",
                        'created_by'        => auth()->id(),
                    ]);
                }
            }
            // If diff == 0, no stock adjustment needed
            
        } else {
            // NEW ALLOCATION SCENARIO
            $totalRequiredForNew = 0;
            foreach ($pendingBatches as $batch) {
                $batchRequired = $newQuantity * $batch->quantity;
                $totalRequiredForNew += $batchRequired;
                Log::info('[CONTROLLER] Batch #' . $batch->id . ' requires: ' . $batchRequired);
            }
            
            Log::info('[CONTROLLER] Total required for new: ' . $totalRequiredForNew . 
                     ', Available: ' . $material->quantity);
            
            if ($material->quantity < $totalRequiredForNew) {
                DB::rollBack();
                Log::error('[CONTROLLER] Insufficient stock for new allocation');
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for pending batches. Available: {$material->quantity}, Required: {$totalRequiredForNew}"
                ], 400);
            }
            
            // Deduct stock for new allocation
            foreach ($pendingBatches as $batch) {
                $qtyToDeduct = $newQuantity * $batch->quantity;
                $material->decrement('quantity', $qtyToDeduct);
                
                Log::info('[CONTROLLER] Deducted ' . $qtyToDeduct . ' for new allocation in Batch #' . $batch->id);
                
                // Record history
                MaterialHistoryModel::create([
                    'raw_material_id'   => $material->id,
                    'old_quantity'      => $material->quantity + $qtyToDeduct,
                    'quantity_change'   => -$qtyToDeduct,
                    'new_quantity'      => $material->fresh()->quantity,
                    'old_unit_cost'     => $material->unit_cost,
                    'unit_cost'         => $material->unit_cost,
                    'total_cost_change' => $qtyToDeduct * $material->unit_cost,
                    'type'              => 'used',
                    'notes'             => "Material '{$material->name}' added to recipe for Batch #{$batch->id} (Used: {$qtyToDeduct})",
                    'created_by'        => auth()->id(),
                ]);
            }
        }
        
        // Create or update the allocation (WITHOUT triggering observer to avoid double counting)
        UsedMaterialModel::withoutEvents(function () use ($existingAllocation, $validated, $material, &$action, &$allocation) {
            if ($existingAllocation) {
                Log::info('[CONTROLLER] Updating existing allocation from ' . 
                         $existingAllocation->quantity_used . ' to ' . $validated['quantity_used']);
                
                $existingAllocation->update([
                    'quantity_used' => $validated['quantity_used'],
                    'unit_cost' => $material->unit_cost,
                    'total_cost' => $material->unit_cost * $validated['quantity_used']
                ]);
                
                $action = 'updated';
                $allocation = $existingAllocation->fresh();
            } else {
                Log::info('[CONTROLLER] Creating new allocation with quantity: ' . $validated['quantity_used']);
                
                $allocation = UsedMaterialModel::create([
                    'batchproduct_id' => $validated['batchproduct_id'],
                    'raw_material_id' => $validated['material_id'],
                    'quantity_used' => $validated['quantity_used'],
                    'unit_cost' => $material->unit_cost,
                    'total_cost' => $material->unit_cost * $validated['quantity_used'],
                    'status' => 'Allocated',
                ]);
                
                $action = 'allocated';
            }
        });
        
        // Update batch product and batch costs
        $this->updateCosts($allocation);
        
        DB::commit();
        
        // Clear relevant caches
        Cache::forget("batchproduct_{$validated['batchproduct_id']}");
        Cache::forget("materials_category_{$material->category_id}");
        
        // Log final stock
        $finalMaterial = RawMaterialModel::find($validated['material_id']);
        Log::info('[CONTROLLER] Final stock for ' . $material->name . ': ' . $finalMaterial->quantity);
        
        return response()->json([
            'success' => true,
            'message' => "Material {$action} successfully!",
            'data' => $allocation
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('[CONTROLLER] Allocation Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Server error occurred: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Update batch and product costs
 */
private function updateCosts(UsedMaterialModel $usedMaterial)
{
    try {
        // Update batch product material cost
        $product = BatchProductModel::with('usedMaterials.rawMaterial')
            ->find($usedMaterial->batchproduct_id);
        
        if ($product) {
            $materialCost = 0;
            foreach ($product->usedMaterials as $row) {
                $materialCost += ($row->rawMaterial->unit_cost ?? 0) * $row->quantity_used;
            }
            
            $product->updateQuietly([
                'material_cost' => $materialCost,
            ]);
            
            Log::info('[COST UPDATE] Product #' . $product->id . ' material cost updated: ' . $materialCost);
        }
        
        // Update pending batch costs
        $batches = BatchModel::where('batchproduct_id', $usedMaterial->batchproduct_id)
            ->where('status', 'Pending')
            ->with('product.usedMaterials.rawMaterial')
            ->get();
        
        Log::info('[COST UPDATE] Updating ' . $batches->count() . ' pending batches');
        
        foreach ($batches as $batch) {
            $materialUnitCost = 0;
            if ($batch->product && $batch->product->usedMaterials) {
                foreach ($batch->product->usedMaterials as $item) {
                    $materialUnitCost += ($item->rawMaterial->unit_cost ?? 0) * $item->quantity_used;
                }
            }
            
            $materialTotal = $materialUnitCost * $batch->quantity;
            $total = $materialTotal + ($batch->labor_cost ?? 0) + ($batch->other_expenses ?? 0);
            
            $batch->updateQuietly([
                'material_cost' => $materialTotal,
                'total_cost' => $total,
                'expected_unit_cost' => $batch->quantity > 0 ? $total / $batch->quantity : 0,
            ]);
            
            Log::info('[COST UPDATE] Batch #' . $batch->id . 
                     ' costs updated - Material: ' . $materialTotal . 
                     ', Total: ' . $total);
        }
        
    } catch (\Exception $e) {
        Log::error('[COST UPDATE] Error: ' . $e->getMessage());
    }
}
private function manuallyAdjustStock(UsedMaterialModel $usedMaterial, $oldQuantity, $newQuantity)
{
    $diff = $newQuantity - $oldQuantity;
    if ($diff === 0) return;
    
    $pendingBatches = BatchModel::where('batchproduct_id', $usedMaterial->batchproduct_id)
        ->where('status', 'Pending')
        ->get();
    
    $rawMaterial = $usedMaterial->rawMaterial;
    if (!$rawMaterial) return;
    
    foreach ($pendingBatches as $batch) {
        $qtyChange = $diff * $batch->quantity;
        
        if ($diff > 0) {
            // Need more material
            if ($rawMaterial->quantity < $qtyChange) {
                throw new \Exception("Insufficient stock for {$rawMaterial->name}");
            }
            $rawMaterial->decrement('quantity', $qtyChange);
        } else {
            // Return excess material
            $rawMaterial->increment('quantity', abs($qtyChange));
        }
        
        // Record history
        MaterialHistoryModel::create([
            'raw_material_id'   => $rawMaterial->id,
            'old_quantity'      => $rawMaterial->quantity + ($diff > 0 ? $qtyChange : -abs($qtyChange)),
            'quantity_change'   => $diff > 0 ? -$qtyChange : abs($qtyChange),
            'new_quantity'      => $rawMaterial->fresh()->quantity,
            'old_unit_cost'     => $rawMaterial->unit_cost,
            'unit_cost'         => $rawMaterial->unit_cost,
            'total_cost_change' => abs($qtyChange) * $rawMaterial->unit_cost,
            'type'              => $diff > 0 ? 'used' : 'adjusted',
            'notes'             => "Manual adjustment for material quantity change in recipe",
            'created_by'        => auth()->id(),
        ]);
    }
}

public function deleteAllocation($id)
{
    DB::beginTransaction();
    try {
        $usedMaterial = UsedMaterialModel::with('rawMaterial')->findOrFail($id);
        $batchproductId = $usedMaterial->batchproduct_id;
        $categoryId = $usedMaterial->rawMaterial->category_id ?? null;
        
        // Get pending batches
        $pendingBatches = BatchModel::where('batchproduct_id', $batchproductId)
            ->where('status', 'Pending')
            ->get();
        
        // Return stock if there are pending batches
        if (!$pendingBatches->isEmpty() && $usedMaterial->rawMaterial) {
            $rawMaterial = $usedMaterial->rawMaterial;
            $quantityToReturn = $usedMaterial->quantity_used;
            
            foreach ($pendingBatches as $batch) {
                $returnQty = $quantityToReturn * $batch->quantity;
                $rawMaterial->increment('quantity', $returnQty);
                
                Log::info('[DELETE] Returned ' . $returnQty . ' for Batch #' . $batch->id);
                
                // Record history
                MaterialHistoryModel::create([
                    'raw_material_id'   => $rawMaterial->id,
                    'old_quantity'      => $rawMaterial->quantity - $returnQty,
                    'quantity_change'   => $returnQty,
                    'new_quantity'      => $rawMaterial->fresh()->quantity,
                    'old_unit_cost'     => $rawMaterial->unit_cost,
                    'unit_cost'         => $rawMaterial->unit_cost,
                    'total_cost_change' => $returnQty * $rawMaterial->unit_cost,
                    'type'              => 'adjusted',
                    'notes'             => "Material '{$rawMaterial->name}' removed from recipe for Batch #{$batch->id} (Returned: {$returnQty})",
                    'created_by'        => auth()->id(),
                ]);
            }
        }
        
        // Delete the allocation WITHOUT triggering observer
        UsedMaterialModel::withoutEvents(function () use ($usedMaterial) {
            $usedMaterial->delete();
        });
        
        // Update costs after deletion
        $this->updateCosts($usedMaterial);
        
        DB::commit();
        
        // Clear caches
        Cache::forget("batchproduct_{$batchproductId}");
        if ($categoryId) {
            Cache::forget("materials_category_{$categoryId}");
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Material removed from allocation'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting allocation: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Could not delete allocation: ' . $e->getMessage()
        ], 500);
    }
}

    public function showUsedMaterials($id)
    {
        // Cache used materials for 1 minute
        $cacheKey = "used_materials_{$id}";
        
        $usedMaterials = Cache::remember($cacheKey, 60, function() use ($id) {
            return UsedMaterialModel::where('batchproduct_id', $id)
                ->with(['rawMaterial' => function($query) {
                    $query->select('id', 'name', 'category_id')
                          ->with(['category' => function($q) {
                              $q->select('id', 'name');
                          }]);
                }])
                ->select('id', 'raw_material_id', 'quantity_used', 'unit_cost', 'total_cost')
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
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'materials' => $usedMaterials
            ]
        ]);
    }

    public function checkAllocation($batchId, $materialId)
    {
        // Simple query with no caching for real-time check
        $allocation = UsedMaterialModel::where([
            'batchproduct_id' => $batchId,
            'raw_material_id' => $materialId
        ])->first(['quantity_used', 'total_cost']);
        
        return response()->json([
            'allocated' => !is_null($allocation),
            'data' => $allocation ? [
                'quantity_used' => $allocation->quantity_used,
                'total_cost' => $allocation->total_cost
            ] : null
        ]);
    }


}