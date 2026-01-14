<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role === 'admin' && $request->filled('user_id')) {
        $userId = $request->user_id; // Admin viewing another user
    } else {
        $userId = Auth::id(); // Normal user
    }

        // Total Sales
        $totalSales = Sale::where('user_id', $userId)->sum('total_amount');
        
        // Total Invoices
        $totalInvoices = Sale::where('user_id', $userId)->count();

        // Top Selling Product
        $topProduct = SaleItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('sale', fn($q) => $q->where('user_id', $userId))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->first();

        // Monthly Sales
        $monthlySalesRaw = Sale::where('user_id', $userId)
            ->whereYear('date', now()->year)
            ->selectRaw('MONTH(date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill missing months
        $monthlySales = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlySales[$i] = $monthlySalesRaw[$i] ?? 0;
        }

        // Calculate Growth
        $thisMonth = $monthlySales[now()->month] ?? 0;
        $lastMonth = $monthlySales[now()->month - 1] ?? 0;
        $growth = $lastMonth > 0 
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2) 
            : ($thisMonth > 0 ? 100 : 0);

        // Product Status
        $productStatus = [
            'Available'   => ProductModel::where('user_id', $userId)->where('quantity', '>', 5)->count(),
            'Low Stock'   => ProductModel::where('user_id', $userId)->whereBetween('quantity', [1, 5])->count(),
            'Out of Stock'=> ProductModel::where('user_id', $userId)->where('quantity', 0)->count(),
        ];

        // Sale Status
        $saleStatus = Sale::where('user_id', $userId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $saleStatus = [
            'Completed' => $saleStatus['completed'] ?? 0,
            'Returned'  => $saleStatus['returned'] ?? 0,
        ];

        // --- DAILY SALES ---
        // Last 30 days
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $dailySalesRaw = Sale::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('DATE(date) as day, COUNT(*) as invoices, SUM(total_amount) as total')
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->get();

        $dailySales = [];
        foreach ($dailySalesRaw as $sale) {
            // Get top product for that day
            $topProductDay = SaleItem::whereHas('sale', fn($q) => $q->where('user_id', $userId)->whereDate('date', $sale->day))
                ->select('product_id', DB::raw('SUM(quantity) as qty'))
                ->groupBy('product_id')
                ->orderByDesc('qty')
                ->with('product')
                ->first();

            $dailySales[$sale->day] = [
                'invoices'    => $sale->invoices,
                'total'       => $sale->total,
                'top_product' => $topProductDay ? $topProductDay->product->name : 'N/A',
            ];
        }

        return view('user.userproducts.dashboard', compact(
            'totalSales',
            'totalInvoices',
            'topProduct',
            'growth',
            'monthlySales',
            'productStatus',
            'saleStatus',
            'dailySales'
        ));
    }

    public function getDashboardData(Request $request)
    {
      $userId = Auth::user()->role === 'admin' && $request->filled('user_id')
        ? $request->user_id
        : Auth::id();
        
        return response()->json([
            'success' => true,
            'data' => [
                'totalSales' => Sale::where('user_id', $userId)->sum('total_amount'),
                'totalInvoices' => Sale::where('user_id', $userId)->count(),
            ],
            'timestamp' => now()->toISOString()
        ]);
    }
}
