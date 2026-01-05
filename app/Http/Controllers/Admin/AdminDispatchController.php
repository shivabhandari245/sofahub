<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DispatchModel;

use Illuminate\Http\Request;

class AdminDispatchController extends Controller
{
public function index()
{
    $dispatches = DispatchModel::with('batch.product.category', 'batch.product.quality', 'user')
        ->whereIn('status', ['Pending', 'In Transit'])
        ->orderBy('created_at', 'desc')
        ->get();

    $users = User::role('user')->get();

    return view('admin.dispatch.dispatch', compact('dispatches', 'users'));
}


    // Distribute batch (partial or full)
    public function distributeBatch(Request $request)
    {
        $request->validate([
            'dispatch_id' => 'required|exists:dispatches,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
            'driver' => 'nullable|string|max:255'
        ]);

        $dispatch = DispatchModel::findOrFail($request->dispatch_id);
        $batch = $dispatch->batch;

        // Calculate total allocated quantity excluding current dispatch
        $totalAllocated = DispatchModel::where('batch_id', $batch->id)
            ->where('id', '!=', $dispatch->id)
            ->sum('quantity');

        $remainingQty = $batch->quantity - $totalAllocated;

        if ($request->quantity > $remainingQty) {
            return response()->json([
                'success' => false,
                'message' => "Quantity exceeds remaining batch quantity ({$remainingQty})."
            ], 400);
        }

        if ($request->quantity < $remainingQty) {
            // Partial distribution: reduce original dispatch quantity and create new dispatched row
            $dispatch->update([
                'quantity' => $remainingQty - $request->quantity,
            ]);

            $newDispatch = DispatchModel::create([
                'batch_id' => $batch->id,
                'user_id' => $request->user_id,
                'quantity' => $request->quantity,
                'driver' => $request->driver,
                'status' => 'In Transit',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Partial batch distributed successfully!',
                'dispatch' => $newDispatch
            ]);

        } else {
            // Full remaining quantity: update current dispatch
            $dispatch->update([
                'user_id' => $request->user_id,
                'driver' => $request->driver,
                'status' => 'In Transit',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch distributed successfully!',
                'dispatch' => $dispatch
            ]);
        }
    }

    // Cancel a dispatch
    public function cancelDispatch(Request $request)
    {
        $request->validate([
            'dispatch_id' => 'required|exists:dispatches,id'
        ]);

        $dispatch = DispatchModel::findOrFail($request->dispatch_id);

        if (!in_array($dispatch->status, ['Pending', 'In Transit'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only Pending or In Transit dispatch can be canceled.'
            ], 400);
        }

        $batch = $dispatch->batch;

        // Restore quantity to remaining pending dispatch
        $pendingDispatch = DispatchModel::where('batch_id', $batch->id)
            ->where('user_id', null)
            ->where('status', 'Pending')
            ->first();

        if ($pendingDispatch) {
            $pendingDispatch->update([
                'quantity' => $pendingDispatch->quantity + $dispatch->quantity
            ]);
        } else {
            // If no pending dispatch exists, make current dispatch pending
            $dispatch->update([
                'user_id' => null,
                'driver' => null,
                'status' => 'Pending',
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Dispatch canceled and restored as pending.',
                'dispatch' => $dispatch
            ]);
        }

        // Delete the canceled dispatch (transit row)
        $dispatch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dispatch canceled and quantity restored to pending.'
        ]);
    }


public function completed()
{
    $dispatches = DispatchModel::with([
            'batch.product.category',
            'batch.product.quality',
            'user'
        ])
        ->where('status', 'Received')
        ->orderBy('received_date', 'desc')
        ->get();

    return view('admin.dispatch.dispatchcompleted', compact('dispatches'));
}


}
