<?php

namespace App\Observers;

use App\Models\BatchModel;
use App\Models\MaterialHistoryModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatchObserver
{
    /**
     * Batch created → deduct stock + history
     */
    public function created(BatchModel $batch)
    {
        DB::transaction(function () use ($batch) {
            $this->deductStock($batch);
        });
    }

    /**
     * Batch updated → adjust stock + history
     */
    public function updated(BatchModel $batch)
    {
        if ($batch->isDirty('quantity')) {
            DB::transaction(function () use ($batch) {
                $this->adjustStock($batch);
            });
        }
    }

    /**
     * Batch deleted → restore stock + history
     */
    public function deleted(BatchModel $batch)
    {
        DB::transaction(function () use ($batch) {
            $this->restoreStock($batch);
        });
    }

    /* ================= HELPERS ================= */

    protected function deductStock(BatchModel $batch)
    {
        if (!$batch->product || $batch->product->usedMaterials->isEmpty()) {
            return;
        }

        foreach ($batch->product->usedMaterials as $used) {
            $raw = $used->rawMaterial;
            if (!$raw) continue;

            $usedQty = $used->quantity_used * $batch->quantity;
            $oldQty  = $raw->quantity;

            if ($oldQty < $usedQty) {
                throw new \Exception("Insufficient stock for {$raw->name}");
            }

            $raw->decrement('quantity', $usedQty);

            // 🔹 Material History (USED)
            MaterialHistoryModel::create([
                'raw_material_id'   => $raw->id,
                'old_quantity'      => $oldQty,
                'quantity_change'   => -$usedQty,
                'new_quantity'      => $raw->quantity,
                'old_unit_cost'     => $raw->unit_cost,
                'unit_cost'         => $raw->unit_cost,
                'total_cost_change' => $usedQty * $raw->unit_cost,
                'type'              => 'used',
                'notes'             => "Used in Batch #{$batch->batch_code}",
                'created_by'        => Auth::id(),
            ]);
        }
    }

    protected function restoreStock(BatchModel $batch)
    {
        if (!$batch->product || $batch->product->usedMaterials->isEmpty()) {
            return;
        }

        foreach ($batch->product->usedMaterials as $used) {
            $raw = $used->rawMaterial;
            if (!$raw) continue;

            $returnQty = $used->quantity_used * $batch->quantity;
            $oldQty    = $raw->quantity;

            $raw->increment('quantity', $returnQty);

            // 🔹 Material History (RETURN / ADJUSTED)
            MaterialHistoryModel::create([
                'raw_material_id'   => $raw->id,
                'old_quantity'      => $oldQty,
                'quantity_change'   => $returnQty,
                'new_quantity'      => $raw->quantity,
                'old_unit_cost'     => $raw->unit_cost,
                'unit_cost'         => $raw->unit_cost,
                'total_cost_change' => $returnQty * $raw->unit_cost,
                'type'              => 'adjusted',
                'notes'             => "Returned from Batch #{$batch->batch_code}",
                'created_by'        => Auth::id(),
            ]);
        }
    }

    protected function adjustStock(BatchModel $batch)
    {
        if (!$batch->product || $batch->product->usedMaterials->isEmpty()) {
            return;
        }

        $oldBatchQty = $batch->getOriginal('quantity');
        $newBatchQty = $batch->quantity;
        $diff        = $newBatchQty - $oldBatchQty;

        if ($diff === 0) return;

        foreach ($batch->product->usedMaterials as $used) {
            $raw = $used->rawMaterial;
            if (!$raw) continue;

            $changeQty = $used->quantity_used * abs($diff);
            $oldQty    = $raw->quantity;

            if ($diff > 0) {
                // More production → use more material
                if ($oldQty < $changeQty) {
                    throw new \Exception("Insufficient stock for {$raw->name}");
                }

                $raw->decrement('quantity', $changeQty);

                $type  = 'used';
                $qtyCh = -$changeQty;
                $note  = "Additional usage for Batch #{$batch->batch_code}";
            } else {
                // Reduced production → return material
                $raw->increment('quantity', $changeQty);

                $type  = 'adjusted';
                $qtyCh = $changeQty;
                $note  = "Returned due to batch quantity reduction #{$batch->batch_code}";
            }

            // 🔹 Material History
            MaterialHistoryModel::create([
                'raw_material_id'   => $raw->id,
                'old_quantity'      => $oldQty,
                'quantity_change'   => $qtyCh,
                'new_quantity'      => $raw->quantity,
                'old_unit_cost'     => $raw->unit_cost,
                'unit_cost'         => $raw->unit_cost,
                'total_cost_change' => $changeQty * $raw->unit_cost,
                'type'              => $type,
                'notes'             => $note,
                'created_by'        => Auth::id(),
            ]);
        }
    }
}
