<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchProductModel;
use App\Models\RawMaterialModel;
use App\Models\UsedMaterialModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            
            // Check stock availability
            if ($material->quantity < $validated['quantity_used']) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock. Available: {$material->quantity} {$material->unit->name}"
                ], 400);
            }
            
            // Check for existing allocation
            $existingAllocation = UsedMaterialModel::where([
                'batchproduct_id' => $validated['batchproduct_id'],
                'raw_material_id' => $validated['material_id']
            ])->first();
            
            if ($existingAllocation) {
                // Update existing allocation
                $existingAllocation->update([
                    'quantity_used' => $validated['quantity_used'],
                    'total_cost' => $material->unit_cost * $validated['quantity_used']
                ]);
                
                $action = 'updated';
                $allocation = $existingAllocation;
            } else {
                // Create new allocation
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
            
            DB::commit();
            
            // Clear relevant caches
            Cache::forget("batchproduct_{$validated['batchproduct_id']}");
            Cache::forget("materials_category_{$material->category_id}");
            
            return response()->json([
                'success' => true,
                'message' => "Material {$action} successfully!",
                'data' => $allocation
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Allocation Error: ' . $e->getMessage());
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
            $usedMaterial = UsedMaterialModel::with('rawMaterial')->findOrFail($id);
            $batchproductId = $usedMaterial->batchproduct_id;
            $categoryId = $usedMaterial->rawMaterial->category_id ?? null;
            
            $usedMaterial->delete();
            
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
            \Log::error('Error deleting allocation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not delete allocation'
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