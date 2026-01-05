<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\ProductModel;
use App\Models\RawMaterialModel;
use App\Models\BatchModel;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Current month calculations
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Total Sales This Month vs Last Month
        $currentMonthSales = Sale::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->sum('total_amount');
        $previousMonthSales = Sale::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_amount');
        $salesChange = $previousMonthSales > 0 
            ? (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100 
            : 0;

        // Profit This Month vs Last Month
        $currentMonthProfit = Sale::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->sum('profit');
        $previousMonthProfit = Sale::whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('profit');
        $profitChange = $previousMonthProfit > 0 
            ? (($currentMonthProfit - $previousMonthProfit) / $previousMonthProfit) * 100 
            : 0;

        // Units Produced
        $unitsProduced = BatchModel::whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->sum('quantity');

        // Total Products
        $totalProducts = ProductModel::count();

        $customers = Customer::count();

        // Recent Sales (Last 5)
        $recentSales = Sale::with('customer')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Sales Last 6 Months for chart
        $salesLast6Months = Sale::select(
            DB::raw("DATE_FORMAT(created_at,'%b') as month"),
            DB::raw("YEAR(created_at) as year"),
            DB::raw("SUM(total_amount) as total")
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
        ->groupBy('month', 'year')
        ->orderBy('year')
        ->orderBy(DB::raw("MONTH(created_at)"))
        ->get()
        ->map(function($item) {
            return [
                'month' => $item->month,
                'total' => $item->total
            ];
        });

        // Top Products by Sales
        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as revenue')
            )
            ->whereBetween('sale_items.created_at', [$currentMonthStart, $currentMonthEnd])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // Monthly Production Trend
        $productionLast6Months = DB::table('batches')
            ->select(
                DB::raw("DATE_FORMAT(created_at,'%b') as month"),
                DB::raw("YEAR(created_at) as year"),
                DB::raw("SUM(quantity) as total_units")
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('month', 'year')
            ->orderBy('year')
            ->orderBy(DB::raw("MONTH(created_at)"))
            ->get()
            ->map(function($item) {
                return [
                    'month' => $item->month,
                    'total_units' => $item->total_units
                ];
            });

        // Material Stock Status
        $materials = RawMaterialModel::select('name', 'quantity')
            ->orderBy('quantity', 'asc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'currentMonthSales',
            'previousMonthSales',
            'salesChange',
            'currentMonthProfit',
            'previousMonthProfit',
            'profitChange',
            'unitsProduced',
            'totalProducts',
            'recentSales',
            'salesLast6Months',
            'productionLast6Months',
            'topProducts',
            'materials',
            'customers'
        ));
    }
}