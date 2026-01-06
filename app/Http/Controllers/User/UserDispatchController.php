<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DispatchModel;
use App\Models\ProductModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserDispatchController extends Controller
{
    // Show dispatch page (Blade will use server-side DataTable)
    public function index()
    {
        return view('user.userproducts.dispatch');
    }

    // Server-side DataTable JSON
    public function serverSideDispatch(Request $request)
    {
        $userId = Auth::id();

        $query = DispatchModel::where('user_id', $userId)
            ->with(['batch.product.category', 'batch.product.quality']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('product', function($row) {
                return $row->batch->product->name ?? '-';
            })
            ->addColumn('category', function($row) {
                return $row->batch->product->category->name ?? '-';
            })
            ->addColumn('quality', function($row) {
                return $row->batch->product->quality->name ?? '-';
            })
            ->addColumn('quantity', function($row) {
                return $row->quantity;
            })
            ->addColumn('unit_cost', function($row) {
                return number_format($row->batch->expected_unit_cost ?? 0, 2);
            })
            ->addColumn('total_cost', function($row) {
                return number_format(($row->batch->expected_unit_cost ?? 0) * $row->quantity, 2);
            })
            ->addColumn('driver', function($row) {
                return $row->driver ?? '-';
            })
            ->addColumn('status', function($row) {
                $status = $row->status;
                $class = match($status) {
                    'Dispatched' => 'bg-warning',
                    'In Transit' => 'bg-info text-dark',
                    'Delivered' => 'bg-primary',
                    'Received' => 'bg-success',
                    default => 'bg-secondary'
                };
                return '<span class="badge '.$class.'">'.$status.'</span>';
            })
            ->addColumn('action', function($row) {
                if($row->status != 'Received'){
                    return '<button class="btn btn-success btn-sm receiveBtn" data-id="'.$row->id.'" data-product="'.($row->batch->product->name ?? '-').'">Confirm Receive</button>';
                }
                return '<span class="text-success fw-bold">Completed</span>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    // Confirm received stock
    public function confirmReceive(Request $request)
    {
        $request->validate([
            'dispatch_id' => 'required|exists:dispatches,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        try {
            $dispatch = DispatchModel::with('batch.product')->findOrFail($request->dispatch_id);

            if ($dispatch->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }

            if (!$dispatch->batch || !$dispatch->batch->product) {
                return response()->json(['error' => 'Batch or product info missing!'], 400);
            }

            DB::transaction(function () use ($dispatch, $request) {

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
                        'user_id' => Auth::id(),
                        'name' => $dispatch->batch->product->name,
                        'category' => $dispatch->batch->product->category->name,
                        'quality' => $dispatch->batch->product->quality->name,
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
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
