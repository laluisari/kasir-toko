<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    // KPI: Total Penjualan Hari Ini dengan Perubahan
    public function getKPITodaysSales()
    {
        $today = Carbon::today();
        $yesterday = Carbon::today()->subDay();
        
        $todaysSales = SaleDocument::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total_price');
        
        $yesterdaysSales = SaleDocument::whereDate('created_at', $yesterday)
            ->where('status', 'completed')
            ->sum('total_price');
        
        $changePercent = $yesterdaysSales > 0 
            ? round((($todaysSales - $yesterdaysSales) / $yesterdaysSales) * 100, 1)
            : 0;

        return response()->json([
            'success' => true,
            'value' => $todaysSales,
            'formatted' => 'Rp ' . number_format($todaysSales, 0, ',', '.'),
            'change_percent' => $changePercent,
            'is_increase' => $changePercent >= 0,
        ]);
    }

    // KPI: Total Penjualan Bulan Ini dengan Perubahan
    public function getKPIMonthSales()
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        $monthSales = SaleDocument::whereBetween('created_at', [$thisMonth, Carbon::now()])
            ->where('status', 'completed')
            ->sum('total_price');
        
        $lastMonthSales = SaleDocument::whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->where('status', 'completed')
            ->sum('total_price');
        
        $changePercent = $lastMonthSales > 0 
            ? round((($monthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : 0;

        return response()->json([
            'success' => true,
            'value' => $monthSales,
            'formatted' => 'Rp ' . number_format($monthSales, 0, ',', '.'),
            'change_percent' => $changePercent,
            'is_increase' => $changePercent >= 0,
        ]);
    }

    // KPI: Total Penjualan Tahun Ini dengan Perubahan
    public function getKPIYearSales()
    {
        $thisYear = Carbon::now()->startOfYear();
        $lastYear = Carbon::now()->subYear()->startOfYear();
        $lastYearEnd = Carbon::now()->subYear()->endOfYear();
        
        $yearSales = SaleDocument::whereBetween('created_at', [$thisYear, Carbon::now()])
            ->where('status', 'completed')
            ->sum('total_price');
        
        $lastYearSales = SaleDocument::whereBetween('created_at', [$lastYear, $lastYearEnd])
            ->where('status', 'completed')
            ->sum('total_price');
        
        $changePercent = $lastYearSales > 0 
            ? round((($yearSales - $lastYearSales) / $lastYearSales) * 100, 1)
            : 0;

        return response()->json([
            'success' => true,
            'value' => $yearSales,
            'formatted' => 'Rp ' . number_format($yearSales, 0, ',', '.'),
            'change_percent' => $changePercent,
            'is_increase' => $changePercent >= 0,
        ]);
    }

    // KPI: Total Transaksi Hari Ini
    public function getKPITodaysTransactions()
    {
        $today = Carbon::today();
        $todaysTransactions = SaleDocument::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'success' => true,
            'value' => $todaysTransactions,
        ]);
    }

    // KPI: Total Item Terjual Hari Ini
    public function getKPITodaysItems()
    {
        $today = Carbon::today();
        $todaysItems = Sale::whereDate('created_at', $today)
            ->sum('quantity');

        return response()->json([
            'success' => true,
            'value' => $todaysItems ?? 0,
        ]);
    }

    // Data: Produk Terlaris (Top 5)
    public function getTopProducts()
    {
        $topProducts = Sale::selectRaw('product_id, SUM(quantity) as total_qty, product_name')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts,
        ]);
    }

    // Data: Produk Stok Terbatas (< 5)
    public function getLowStockProducts()
    {
        $lowStockProducts = Product::with('category')
            ->where('stock', '<', 5)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockProducts,
        ]);
    }

    // Data: Penjualan Chart by Range
    public function getSalesChartByRange(Request $request)
    {
        $range = $request->get('range', '1month');
        $chartData = [];

        switch ($range) {
            case 'week':
                // Last Week - Daily breakdown (today + last 7 days)
                $startDate = Carbon::now()->subDays(7);
                $endDate = Carbon::now()->endOfDay();
                
                $salesData = SaleDocument::selectRaw('DATE(created_at) as date, SUM(total_price) as total')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();

                // Ensure all 8 days are present (fill missing days with 0)
                $allDates = collect();
                for ($i = 7; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i)->format('Y-m-d');
                    $allDates->push($date);
                }

                $dateMap = $salesData->keyBy('date')->toArray();
                $salesArray = $allDates->map(function($date) use ($dateMap) {
                    return $dateMap[$date]['total'] ?? 0;
                })->toArray();

                $chartData = [
                    'dates' => $allDates->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray(),
                    'sales' => $salesArray,
                    'range' => 'Minggu Terakhir',
                ];
                break;

            case '1month':
                // This Month - Daily breakdown
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                
                $salesData = SaleDocument::selectRaw('DATE(created_at) as date, SUM(total_price) as total')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();

                $chartData = [
                    'dates' => $salesData->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d'))->toArray(),
                    'sales' => $salesData->pluck('total')->toArray(),
                    'range' => 'Bulan Ini',
                ];
                break;

            case '3months':
                // Last 3 Months - Monthly breakdown
                $startDate = Carbon::now()->subMonths(2)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                
                $salesData = SaleDocument::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_price) as total')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('month')
                    ->orderBy('month', 'asc')
                    ->get();

                $chartData = [
                    'dates' => $salesData->map(fn($item) => Carbon::parse($item->month)->format('M y'))->toArray(),
                    'sales' => $salesData->pluck('total')->toArray(),
                    'range' => '3 Bulan Terakhir',
                ];
                break;

            case '6months':
                // Last 6 Months - Monthly breakdown
                $startDate = Carbon::now()->subMonths(5)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                
                $salesData = SaleDocument::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_price) as total')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('month')
                    ->orderBy('month', 'asc')
                    ->get();

                $chartData = [
                    'dates' => $salesData->map(fn($item) => Carbon::parse($item->month)->format('M y'))->toArray(),
                    'sales' => $salesData->pluck('total')->toArray(),
                    'range' => '6 Bulan Terakhir',
                ];
                break;

            default:
                // Default: This Month
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                
                $salesData = SaleDocument::selectRaw('DATE(created_at) as date, SUM(total_price) as total')
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('date')
                    ->orderBy('date', 'asc')
                    ->get();

                $chartData = [
                    'dates' => $salesData->pluck('date')->map(fn($d) => Carbon::parse($d)->format('d'))->toArray(),
                    'sales' => $salesData->pluck('total')->toArray(),
                    'range' => 'Bulan Ini',
                ];
        }

        return response()->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }

    // Data: Payment Methods Today
    public function getPaymentMethods()
    {
        $today = Carbon::today();
        $paymentMethods = SaleDocument::selectRaw('payment_method, COUNT(*) as count, SUM(total_price) as total')
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paymentMethods,
            'todaysSales' => SaleDocument::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('total_price'),
        ]);
    }

    // Data: Penjualan per Kategori Hari Ini
    public function getCategoryStats()
    {
        $today = Carbon::today();
        $categoryStats = Sale::selectRaw('products.category_id, categories.name, COUNT(*) as count, SUM(quantity) as total_qty')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereDate('sales.created_at', $today)
            ->groupBy('products.category_id', 'categories.name')
            ->orderByDesc('total_qty')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categoryStats,
        ]);
    }
}
