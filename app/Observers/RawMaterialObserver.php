<?php

namespace App\Observers;

use App\Models\RawMaterialModel;
use App\Models\BatchModel;

class RawMaterialObserver
{
    public function updated(RawMaterialModel $raw)
    {
        if ($raw->isDirty('unit_cost')) {
            $this->updateUsedMaterialCosts($raw);
            $this->updateBatchProductCosts($raw);
            $this->updatePendingBatchCosts($raw);
        }
    }

    /* ================= HELPERS ================= */

    protected function updateUsedMaterialCosts(RawMaterialModel $raw)
    {
        foreach ($raw->usedMaterials as $used) {
            $used->updateQuietly([
                'unit_cost' => $raw->unit_cost,
                'total_cost' => $raw->unit_cost * $used->quantity_used,
            ]);
        }
    }

    protected function updateBatchProductCosts(RawMaterialModel $raw)
    {
        foreach ($raw->usedMaterials as $used) {
            $product = $used->batchproduct;

            $materialCost = $product->usedMaterials->sum(function ($row) {
                return ($row->rawMaterial->unit_cost ?? 0) * $row->quantity_used;
            });

            $product->updateQuietly([
                'material_cost' => $materialCost,
            ]);
        }
    }

    protected function updatePendingBatchCosts(RawMaterialModel $raw)
    {
        foreach ($raw->usedMaterials as $used) {
            $batches = BatchModel::where('batchproduct_id', $used->batchproduct_id)
                ->where('status', 'Pending')
                ->with('product.usedMaterials.rawMaterial')
                ->get();

            foreach ($batches as $batch) {
                $materialUnitCost = $batch->product->usedMaterials->sum(function ($item) {
                    return ($item->rawMaterial->unit_cost ?? 0) * $item->quantity_used;
                });

                $materialTotal = $materialUnitCost * $batch->quantity;
                $total = $materialTotal + $batch->labor_cost + $batch->other_expenses;

                $batch->updateQuietly([
                    'material_cost' => $materialTotal,
                    'total_cost' => $total,
                    'expected_unit_cost' => $batch->quantity > 0 ? $total / $batch->quantity : 0,
                ]);
            }
        }
    }
}
