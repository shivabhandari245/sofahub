<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DispatchModel;
use App\Models\ProductModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserDispatchController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Base query
        $query = DispatchModel::where('user_id', $userId)
            ->with(['batch.product.category', 'batch.product.quality']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by product name
        if ($request->has('search') && $request->search !== '') {
            $query->whereHas('batch.product', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%');
            });
        }

        $dispatches = $query->latest()->get();

        return view('user.userproducts.dispatch', [
            'dispatches' => $dispatches,
            'search' => $request->search ?? '',
            'status' => $request->status ?? 'all'
        ]);
    }
    public function confirmReceive(Request $request)
{
    $request->validate([
        'dispatch_id' => 'required|exists:dispatches,id',
        'remarks' => 'nullable|string|max:255',
    ]);

    try {
        $dispatch = DispatchModel::with('batch.product')->findOrFail($request->dispatch_id);

        // Ensure the logged-in user is the recipient
        if ($dispatch->user_id !== auth::id()) {
            return response()->json(['error' => 'You are not authorized to receive this dispatch.'], 403);
        }

        if (!$dispatch->batch || !$dispatch->batch->product) {
            return response()->json(['error' => 'Batch or product info missing!'], 400);
        }

        DB::transaction(function () use ($dispatch, $request) {

            // Update dispatch status
            $dispatch->update([
                'status' => 'Received',
                'remarks' => $request->remarks ?? null,
                'received_date' => now(),
                'delivered_date' => now()
            ]);

            $unitCost = $dispatch->batch->expected_unit_cost ?? 0;
            $totalCost = $unitCost * $dispatch->quantity;

        ProductModel::firstOrCreate(
                ['dispatch_id' => $dispatch->id],
                [
                    'user_id' => auth::id(),
                    'name'=> $dispatch->batch->product->name,
                    'category'=>$dispatch->batch->product->category->name,
                    'quality'=>$dispatch->batch->product->quality->name,
                    'quantity' => $dispatch->quantity,
                    'cost_per_product' => $unitCost,
                    'total_cost' => $totalCost,
                    'source' => 'From Admin'
                ]
            );
        });

        return response()->json([
            'success' => true,
            'id' => $dispatch->id,
            'product_name' => $dispatch->batch->product->name,
            'added_quantity' => $dispatch->quantity,
            'status' => $dispatch->status
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}


}