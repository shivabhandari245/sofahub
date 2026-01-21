<?php

namespace App\Observers;

use App\Models\UsedMaterialModel;
use App\Models\BatchModel;
use Illuminate\Support\Facades\DB;
use App\Models\MaterialHistoryModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsedMaterialObserver
{
    public function created(UsedMaterialModel $used)
    {
        Log::info('[OBSERVER] Created event fired for UsedMaterial #' . $used->id . 
                 ', Material: ' . ($used->rawMaterial->name ?? 'Unknown') . 
                 ', Quantity: ' . $used->quantity_used);
        
        DB::transaction(function () use ($used) {
            $this->updateUsedMaterialCost($used);
            $this->updateBatchProductCost($used);
            $this->updatePendingBatchCosts($used);
            $this->adjustStockForPendingBatches($used, 'added');
        });
    }

    public function updated(UsedMaterialModel $used)
    {
        Log::info('[OBSERVER] Updated event fired for UsedMaterial #' . $used->id . 
                 ', Material: ' . ($used->rawMaterial->name ?? 'Unknown') . 
                 ', Original Qty: ' . ($used->getOriginal('quantity_used') ?? 0) . 
                 ', New Qty: ' . $used->quantity_used .
                 ', Dirty: ' . json_encode($used->getDirty()));
        
        // Skip if only timestamps were updated
        if ($used->isDirty(['created_at', 'updated_at']) && !$used->isDirty(['quantity_used', 'unit_cost', 'total_cost'])) {
            Log::info('[OBSERVER] Only timestamps changed, skipping');
            return;
        }
        
        DB::transaction(function () use ($used) {
            $this->updateUsedMaterialCost($used);
            $this->updateBatchProductCost($used);
            $this->updatePendingBatchCosts($used);
            
            // If quantity_used changed, adjust stock
            if ($used->isDirty('quantity_used')) {
                Log::info('[OBSERVER] Quantity changed, adjusting stock');
                $this->adjustStockForPendingBatches($used, 'updated');
            }
        });
    }

    public function deleted(UsedMaterialModel $used)
    {
        Log::info('[OBSERVER] Deleted event fired for UsedMaterial #' . $used->id);
        
        DB::transaction(function () use ($used) {
            $this->updateBatchProductCost($used);
            $this->updatePendingBatchCosts($used);
            $this->adjustStockForPendingBatches($used, 'deleted');
        });
    }

    /* ================= IMPROVED STOCK ADJUSTMENT ================= */
    protected function adjustStockForPendingBatches(UsedMaterialModel $used, $action)
    {
        Log::info('[OBSERVER] adjustStockForPendingBatches called', [
            'action' => $action,
            'material_id' => $used->raw_material_id,
            'quantity_used' => $used->quantity_used,
            'batchproduct_id' => $used->batchproduct_id
        ]);
        
        // Get all pending batches using this product
        $pendingBatches = BatchModel::where('batchproduct_id', $used->batchproduct_id)
            ->where('status', 'Pending')
            ->get();

        Log::info('[OBSERVER] Found ' . $pendingBatches->count() . ' pending batches');
        
        if ($pendingBatches->isEmpty()) {
            Log::info('[OBSERVER] No pending batches, nothing to adjust');
            return; // No pending batches, nothing to adjust
        }

        foreach ($pendingBatches as $batch) {
            $raw = $used->rawMaterial;
            if (!$raw) {
                Log::warning('[OBSERVER] Raw material not found for UsedMaterial #' . $used->id);
                continue;
            }

            $oldQty = $raw->quantity;
            $batchQty = $batch->quantity;
            
            Log::info('[OBSERVER] Processing Batch #' . $batch->id . 
                     ', Batch Qty: ' . $batchQty . 
                     ', Material Qty: ' . $oldQty);

            switch ($action) {
                case 'added':
                    // New material added to recipe
                    $usedQty = $used->quantity_used * $batchQty;
                    
                    Log::info('[OBSERVER] ADDED - Required: ' . $usedQty . 
                             ', Available: ' . $raw->quantity);
                    
                    if ($raw->quantity < $usedQty) {
                        $error = "Insufficient stock for {$raw->name}. Available: {$raw->quantity}, Required: {$usedQty}";
                        Log::error('[OBSERVER] ' . $error);
                        throw new \Exception($error);
                    }
                    
                    $raw->decrement('quantity', $usedQty);
                    $quantityChange = -$usedQty;
                    $note = "Material '{$raw->name}' added to recipe for Batch #{$batch->id} (Quantity: {$usedQty})";
                    
                    Log::info('[OBSERVER] Stock deducted: ' . $usedQty . 
                             ', New stock: ' . $raw->fresh()->quantity);
                    break;

                case 'updated':
                    // Material quantity in recipe changed
                    $originalQty = $used->getOriginal('quantity_used') ?? 0;
                    $newQty = $used->quantity_used;
                    $diff = $newQty - $originalQty;
                    
                    Log::info('[OBSERVER] UPDATED - Original: ' . $originalQty . 
                             ', New: ' . $newQty . 
                             ', Diff: ' . $diff);
                    
                    if ($diff === 0) {
                        Log::info('[OBSERVER] No quantity change, skipping stock adjustment');
                        continue 2; // Continue to next batch
                    }
                    
                    $qtyChange = $diff * $batchQty;
                    
                    Log::info('[OBSERVER] Total change for batch: ' . $qtyChange);
                    
                    if ($diff > 0) {
                        // Need more material
                        if ($raw->quantity < $qtyChange) {
                            $error = "Insufficient stock for {$raw->name}. Available: {$raw->quantity}, Additional required: {$qtyChange}";
                            Log::error('[OBSERVER] ' . $error);
                            throw new \Exception($error);
                        }
                        $raw->decrement('quantity', $qtyChange);
                        $quantityChange = -$qtyChange;
                        $note = "Material '{$raw->name}' quantity increased in recipe for Batch #{$batch->id} (Additional: {$qtyChange})";
                        
                        Log::info('[OBSERVER] Additional stock deducted: ' . $qtyChange . 
                                 ', New stock: ' . $raw->fresh()->quantity);
                    } else {
                        // Return excess material
                        $raw->increment('quantity', abs($qtyChange));
                        $quantityChange = abs($qtyChange);
                        $note = "Material '{$raw->name}' quantity decreased in recipe for Batch #{$batch->id} (Returned: " . abs($qtyChange) . ")";
                        
                        Log::info('[OBSERVER] Stock returned: ' . abs($qtyChange) . 
                                 ', New stock: ' . $raw->fresh()->quantity);
                    }
                    break;

                case 'deleted':
                    // Material removed from recipe - return stock
                    $usedQty = $used->quantity_used * $batchQty;
                    $raw->increment('quantity', $usedQty);
                    $quantityChange = $usedQty;
                    $note = "Material '{$raw->name}' removed from recipe for Batch #{$batch->id} (Returned: {$usedQty})";
                    
                    Log::info('[OBSERVER] Stock returned on delete: ' . $usedQty . 
                             ', New stock: ' . $raw->fresh()->quantity);
                    break;
            }

            // Update material history
            if (isset($quantityChange) && $quantityChange != 0) {
                MaterialHistoryModel::create([
                    'raw_material_id'   => $raw->id,
                    'old_quantity'      => $oldQty,
                    'quantity_change'   => $quantityChange,
                    'new_quantity'      => $raw->fresh()->quantity,
                    'old_unit_cost'     => $raw->unit_cost,
                    'unit_cost'         => $raw->unit_cost,
                    'total_cost_change' => abs($quantityChange) * $raw->unit_cost,
                    'type'              => ($quantityChange > 0) ? 'adjusted' : 'used',
                    'notes'             => $note,
                    'created_by'        => Auth::id(),
                ]);
                
                Log::info('[OBSERVER] Material history recorded: ' . $note);
            }
        }
    }

    /* ================= EXISTING HELPERS ================= */
    protected function updateUsedMaterialCost(UsedMaterialModel $used)
    {
        $unitCost = $used->rawMaterial->unit_cost ?? 0;

        // Use updateQuietly to prevent recursion
        $used->updateQuietly([
            'unit_cost' => $unitCost,
            'total_cost' => $unitCost * $used->quantity_used,
        ]);
        
        Log::info('[OBSERVER] Updated used material cost: Unit Cost=' . $unitCost . 
                 ', Total Cost=' . ($unitCost * $used->quantity_used));
    }

    protected function updateBatchProductCost(UsedMaterialModel $used)
    {
        $product = $used->batchproduct()->with('usedMaterials.rawMaterial')->first();
        
        if (!$product) {
            Log::warning('[OBSERVER] Batch product not found for UsedMaterial #' . $used->id);
            return;
        }

        $materialCost = 0;
        foreach ($product->usedMaterials as $row) {
            $materialCost += ($row->rawMaterial->unit_cost ?? 0) * $row->quantity_used;
        }

        $product->updateQuietly([
            'material_cost' => $materialCost,
        ]);
        
        Log::info('[OBSERVER] Updated batch product cost: ' . $materialCost . 
                 ' for Product #' . $product->id);
    }

    protected function updatePendingBatchCosts(UsedMaterialModel $used)
    {
        $batches = BatchModel::where('batchproduct_id', $used->batchproduct_id)
            ->where('status', 'Pending')
            ->with('product.usedMaterials.rawMaterial')
            ->get();

        Log::info('[OBSERVER] Updating costs for ' . $batches->count() . ' pending batches');
        
        foreach ($batches as $batch) {
            $materialUnitCost = 0;
            if ($batch->product && $batch->product->usedMaterials) {
                foreach ($batch->product->usedMaterials as $item) {
                    $materialUnitCost += ($item->rawMaterial->unit_cost ?? 0) * $item->quantity_used;
                }
            }

            $materialTotal = $materialUnitCost * $batch->quantity;
            $total = $materialTotal + $batch->labor_cost + $batch->other_expenses;

            $batch->updateQuietly([
                'material_cost' => $materialTotal,
                'total_cost' => $total,
                'expected_unit_cost' => $batch->quantity > 0 ? $total / $batch->quantity : 0,
            ]);
            
            Log::info('[OBSERVER] Updated batch #' . $batch->id . 
                     ' costs - Material: ' . $materialTotal . 
                     ', Total: ' . $total);
        }
    }
}