<?php

namespace App\Observers;

use App\Models\UsedMaterialModel;
use App\Models\BatchModel;

class UsedMaterialObserver
{
    public function created(UsedMaterialModel $used)
    {
        $this->updateUsedMaterialCost($used);
        $this->updateBatchProductCost($used);
        $this->updatePendingBatchCosts($used);
    }

    public function updated(UsedMaterialModel $used)
    {
        $this->updateUsedMaterialCost($used);
        $this->updateBatchProductCost($used);
        $this->updatePendingBatchCosts($used);
    }

    public function deleted(UsedMaterialModel $used)
    {
        $this->updateBatchProductCost($used);
        $this->updatePendingBatchCosts($used);
    }

    /* ================= HELPERS ================= */

    protected function updateUsedMaterialCost(UsedMaterialModel $used)
    {
        $unitCost = $used->rawMaterial->unit_cost ?? 0;

        $used->updateQuietly([
            'unit_cost' => $unitCost,
            'total_cost' => $unitCost * $used->quantity_used,
        ]);
    }

    protected function updateBatchProductCost(UsedMaterialModel $used)
    {
        $product = $used->batchproduct;

        $materialCost = $product->usedMaterials->sum(function ($row) {
            return ($row->rawMaterial->unit_cost ?? 0) * $row->quantity_used;
        });

        $product->updateQuietly([
            'material_cost' => $materialCost,
        ]);
    }

    protected function updatePendingBatchCosts(UsedMaterialModel $used)
    {
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
