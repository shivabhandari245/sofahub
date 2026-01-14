<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BatchModel;
use Spatie\Permission\Models\Role;
use App\Models\BatchProductModel;
use App\Models\ProductCategoryModel;
use App\Models\ProductQualityModel;
use App\Models\DispatchModel;
use App\Models\RawMaterialModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class BatchController extends Controller
{



public function index(Request $request)
{
    try {
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 10); // default 10 per page

        // STEP 1: Auto mark delayed batches in DB
        BatchModel::where('status', 'Pending')
            ->whereDate('expected_completion', '<', now())
            ->update(['status' => 'Delayed']);

        // STEP 2: Base query
        $query = BatchModel::with(['product.category', 'product.quality'])
            ->whereIn('status', ['Pending', 'Delayed', 'In Progress']);

        // 🔍 Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('leader_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Optional status filter from dropdown
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // ⚡ AJAX response with pagination
            if ($request->ajax()) {
                $batches = $query->latest()->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'batches' => $batches->items(), // only the actual batch array
                    'pagination' => [
                        'current_page' => $batches->currentPage(),
                        'last_page' => $batches->lastPage(),
                        'per_page' => $batches->perPage(),
                        'total' => $batches->total(),
                    ],
                    'summary' => [
                        'totalBatches'   => BatchModel::count(),
                        'savedProducts'  => BatchProductModel::count(),
                        'pending'        => BatchModel::where('status', 'Pending')->count(),
                        'delayed'        => BatchModel::where('status', 'Delayed')->count(),
                    ]
                ]);
            }


        // 🧾 Page load data
        $batchproducts = BatchProductModel::latest()->get();
        $productcategories = ProductCategoryModel::latest()->get();
        $productqualities = ProductQualityModel::latest()->get();

        return view('admin.production.production', compact(
            'batchproducts',
            'productcategories',
            'productqualities'
        ));
    } catch (\Throwable $e) {
        Log::error($e);
        abort(500);
    }
}





public function getUsersForDistribution(): JsonResponse
{
    try {
        $users = \App\Models\User::role('user') // Spatie helper
                     ->select('id', 'name', 'email')
                     ->orderBy('name', 'asc')
                     ->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    } catch (\Throwable $e) {
        Log::error('BatchController@getUsersForDistribution failed: ' . $e->getMessage(), [
            'exception' => $e
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch users.'
        ], 500);
    }
}

public function store(Request $request)
{
    $rules = [
        'batchproduct_id' => 'required|exists:batch_products,id',
        'leader_name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'start_date' => 'required|date',
        'expected_completion' => 'required|date|after_or_equal:start_date',
        'labor_cost' => 'nullable|numeric|min:0',
        'other_expenses' => 'nullable|numeric|min:0',
    ];

    try {
        $validated = $request->validate($rules);

        DB::beginTransaction();

        $batchProduct = BatchProductModel::with('usedMaterials.rawMaterial')
            ->findOrFail($validated['batchproduct_id']);

        // ❌ Block if no materials assigned
        if ($batchProduct->usedMaterials->isEmpty()) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No raw materials assigned to this product. Please assign materials first.'
            ], 400);
        }

        $requestedQty = $validated['quantity'];

        // ✅ Check stock availability
        $insufficient = [];
        foreach ($batchProduct->usedMaterials as $used) {
            if (!$used->rawMaterial) continue;

            $required = $used->quantity_used * $requestedQty;
            $available = $used->rawMaterial->quantity ?? 0;

            if ($available < $required) {
                $insufficient[] = [
                    'material' => $used->rawMaterial->name,
                    'required' => $required,
                    'available' => $available,
                    'shortage' => $required - $available
                ];
            }
        }

        if (!empty($insufficient)) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Insufficient raw materials.',
                'insufficient_materials' => $insufficient
            ], 400);
        }

        // ✅ Calculate material cost
        $materialCost = 0;
        foreach ($batchProduct->usedMaterials as $used) {
            $unitCost = $used->rawMaterial->unit_cost ?? 0;
            $materialCost += $unitCost * $used->quantity_used * $requestedQty;
        }

        $labor = $validated['labor_cost'] ?? 0;
        $other = $validated['other_expenses'] ?? 0;

        $totalCost = $materialCost + $labor + $other;
        $expectedUnitCost = $totalCost / $requestedQty;

        // ✅ Create batch (NO stock logic here)
        $batch = BatchModel::create([
            ...$validated,
            'status' => 'Pending',
            'material_cost' => $materialCost,
            'total_cost' => $totalCost,
            'expected_unit_cost' => $expectedUnitCost,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Batch created successfully!',
            'batch' => $batch
        ]);

    } catch (ValidationException $ve) {
        return response()->json([
            'success' => false,
            'errors' => $ve->errors()
        ], 422);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('BatchController@store failed', [
            'exception' => $e,
            'input' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to create batch.'
        ], 500);
    }
}




public function update(Request $request, $id)
{
    $rules = [
        'batchproduct_id' => 'required|exists:batch_products,id',
        'leader_name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'start_date' => 'required|date',
        'expected_completion' => 'required|date|after_or_equal:start_date',
        'labor_cost' => 'nullable|numeric|min:0',
        'other_expenses' => 'nullable|numeric|min:0',
    ];

    try {
        $validated = $request->validate($rules);

        DB::beginTransaction();

        $batch = BatchModel::findOrFail($id);

        $batchProduct = BatchProductModel::with('usedMaterials.rawMaterial')
            ->findOrFail($validated['batchproduct_id']);

        // ❌ Block if no materials
        if ($batchProduct->usedMaterials->isEmpty()) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No raw materials assigned to this product.'
            ], 400);
        }

        $requestedQty = $validated['quantity'];

        // ✅ Check stock for NET increase only
        $netChange = $requestedQty - $batch->quantity;

        if ($netChange > 0) {
            $insufficient = [];

            foreach ($batchProduct->usedMaterials as $used) {
                if (!$used->rawMaterial) continue;

                $required = $used->quantity_used * $netChange;
                $available = $used->rawMaterial->quantity ?? 0;

                if ($available < $required) {
                    $insufficient[] = [
                        'material' => $used->rawMaterial->name,
                        'required' => $required,
                        'available' => $available,
                        'shortage' => $required - $available
                    ];
                }
            }

            if (!empty($insufficient)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient raw materials for updated quantity.',
                    'insufficient_materials' => $insufficient
                ], 400);
            }
        }

        // ✅ Recalculate material cost
        $materialCost = 0;
        foreach ($batchProduct->usedMaterials as $used) {
            $unitCost = $used->rawMaterial->unit_cost ?? 0;
            $materialCost += $unitCost * $used->quantity_used * $requestedQty;
        }

        $labor = $validated['labor_cost'] ?? $batch->labor_cost ?? 0;
        $other = $validated['other_expenses'] ?? $batch->other_expenses ?? 0;

        $totalCost = $materialCost + $labor + $other;
        $expectedUnitCost = $totalCost / $requestedQty;

        // ✅ Update batch (Observer handles stock)
        $batch->update([
            ...$validated,
            'material_cost' => $materialCost,
            'total_cost' => $totalCost,
            'expected_unit_cost' => $expectedUnitCost,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Batch updated successfully!',
            'batch' => $batch->fresh(['product', 'product.category', 'product.quality'])
        ]);

    } catch (ValidationException $ve) {
        return response()->json([
            'success' => false,
            'errors' => $ve->errors()
        ], 422);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('BatchController@update failed', [
            'exception' => $e,
            'id' => $id
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to update batch.'
        ], 500);
    }
}





    public function destroy($id)
    {
        try {
            $batch = BatchModel::findOrFail($id);
            $batch->delete();
            return response()->json(['success' => true, 'message' => 'Batch deleted successfully.']);
        } catch (\Throwable $e) {
            Log::error('BatchController@destroy failed: ' . $e->getMessage(), ['exception' => $e, 'id' => $id]);
            return response()->json(['success' => false, 'message' => 'Failed to delete batch: ' . $e->getMessage()], 500);
        }
    }

protected function recalcPendingBatchesForProduct(BatchProductModel $product)
 {
    $batches = BatchModel::where('batchproduct_id', $product->id)
        ->where('status', 'Pending')
        ->with('product.usedMaterials.rawMaterial')
        ->get();

    foreach ($batches as $batch) {
        $materialCost = 0;

        foreach ($batch->product->usedMaterials as $used) {
            $materialCost += ($used->quantity_per_unit ?? 0) * ($used->rawMaterial->unit_cost ?? 0) * $batch->quantity;

            // Deduct material stock if batch is newly created or materials changed
            if (!$batch->materials_deducted) {
                $used->rawMaterial->decrement('quantity', $used->quantity_per_unit * $batch->quantity);
            }
        }

        $totalCost = $materialCost + ($batch->labor_cost ?? 0) + ($batch->other_expenses ?? 0);
        $expectedUnitCost = $batch->quantity > 0 ? ($totalCost / $batch->quantity) : 0;

        $batch->update([
            'total_cost' => $totalCost,
            'expected_unit_cost' => $expectedUnitCost,
            'materials_deducted' => true, // flag to prevent double deduction
        ]);
    }
}


public function completeBatch(Request $request, $id)
{
    try {
        $batch = BatchModel::with(['product.usedMaterials.rawMaterial'])->findOrFail($id);

        // Final material cost calculation
        $materialCost = 0;
        foreach ($batch->product->usedMaterials as $used) {
            $materialCost += ($used->quantity_used ?? 0) * ($used->rawMaterial->unit_cost ?? 0) * $batch->quantity;
        }

        $labor = $request->labor_cost ?? $batch->labor_cost ?? 0;
        $other = $request->other_expenses ?? $batch->other_expenses ?? 0;

        $totalCost = $materialCost + $labor + $other;
        $expectedUnitCost = $batch->quantity > 0 ? ($totalCost / $batch->quantity) : 0;

        // Update batch
        $batch->update([
            'labor_cost' => $labor,
            'other_expenses' => $other,
            'material_cost' => $materialCost,
            'total_cost' => $totalCost,
            'expected_unit_cost' => $expectedUnitCost,
            'status' => 'Completed',
        ]);

        // Create dispatch automatically
        DispatchModel::create([
            'batch_id' => $batch->id,
            'quantity' => $batch->quantity,
            'status' => 'Pending',
            'user_id' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Batch completed successfully and sent to Dispatch page!',
            'batch' => $batch->fresh()
        ]);
    } catch (\Throwable $e) {
        Log::error('BatchController@completeBatch failed: ' . $e->getMessage(), [
            'exception' => $e,
            'batch_id' => $id,
            'input' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to complete batch: ' . $e->getMessage()
        ], 500);
    }
}
    public function materials($id)
    {
        try {
            $batch = BatchModel::with(['product.usedMaterials.rawMaterial'])->findOrFail($id);
            return view('admin.production.materials', compact('batch'));
        } catch (\Throwable $e) {
            Log::error('BatchController@materials failed: ' . $e->getMessage(), ['exception' => $e, 'id' => $id]);
            abort(500, 'Failed to load batch materials.');
        }
    }

    public function getAllocatedQuantity($batchId): JsonResponse
    {
        try {
            $allocatedQuantities = DispatchModel::where('batch_id', $batchId)
                ->select('user_id', DB::raw('SUM(quantity) as allocated_quantity'))
                ->groupBy('user_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'user_id' => $item->user_id,
                        'allocated_quantity' => (int)$item->allocated_quantity
                    ];
                });

            return response()->json([
                'success' => true,
                'allocatedQuantities' => $allocatedQuantities
            ]);
        } catch (\Throwable $e) {
            Log::error('BatchController@getAllocatedQuantity failed: ' . $e->getMessage(), ['exception' => $e, 'batchId' => $batchId]);
            return response()->json(['success' => false, 'message' => 'Error fetching allocated quantities: ' . $e->getMessage()], 500);
        }
    }

public function getBatchData($batchId): JsonResponse
{
    try {
        // Only load necessary columns and eager load minimal data
$batch = BatchModel::select(
    'id',
    'batchproduct_id',
    'leader_name',
    'quantity',
    'start_date',
    'expected_completion',
    'labor_cost',
    'other_expenses',
    'status'
)->findOrFail($batchId);



        // Aggregate allocated quantities directly in DB
        $allocatedQuantities = DispatchModel::where('batch_id', $batchId)
            ->groupBy('user_id')
            ->select('user_id', DB::raw('SUM(quantity) as allocated_quantity'))
            ->pluck('allocated_quantity', 'user_id')
            ->map(fn($q) => (int)$q);

        // Only needed user fields
        $users = User::role('user')
            ->select('id', 'name', 'email')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'batch' => $batch,
            'users' => $users,
            'allocated' => $allocatedQuantities
        ]);

    } catch (\Throwable $e) {
        Log::error('BatchController@getBatchData failed: ' . $e->getMessage(), [
            'exception' => $e,
            'batchId' => $batchId
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error fetching batch data'
        ], 500);
    }
}



    protected function validateStock($product, $quantity): array
    {
        return ['success' => true];
    }


    protected function restoreStock($batch): void
    {
        if (app()->environment('local')) {
            Log::info('restoreStock called (no-op under Option A)', ['batch_id' => $batch->id ?? null]);
        }
    }

public function viewcompletebatches(Request $request)
{
    if ($request->ajax()) {
        $search = $request->input('search');

        $query = BatchModel::with(['product.category', 'product.quality'])
            ->where('status', 'Completed');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('leader_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', fn($p) => $p->where('name', 'LIKE', "%{$search}%"));
            });
        }

        return response()->json([
            'success' => true,
            'batches' => $query->latest()->get()
        ]);
    }

    return view('admin.production.completedbatches');
}




}
