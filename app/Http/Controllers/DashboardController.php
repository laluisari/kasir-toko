<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    const CACHE_TTL = 120;
    const TOP_PRODUCTS_CACHE_TTL = 3600;

    public function index()
    {
        return view('admin.dashboard');
    }

    // Ringkasan dashboard dalam satu request (konsolidasi semua panel)
    public function summary()
    {
        $summary = Cache::remember('dashboard.summary', self::CACHE_TTL, function () {
            $todayStart = Carbon::today();
            $tomorrowStart = $todayStart->copy()->addDay();
            $yesterdayStart = $todayStart->copy()->subDay();

            $monthStart = Carbon::now()->startOfMonth();
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

            $yearStart = Carbon::now()->startOfYear();
            $lastYearStart = Carbon::now()->subYear()->startOfYear();
            $lastYearEnd = Carbon::now()->subYear()->endOfYear();

            $todaySales = $this->completedSalesInRange($todayStart, $tomorrowStart);
            $yesterdaySales = $this->completedSalesInRange($yesterdayStart, $todayStart);
            $monthSales = $this->completedSalesInRange($monthStart, Carbon::now());
            $lastMonthSales = $this->completedSalesInRange($lastMonthStart, $lastMonthEnd);
            $yearSales = $this->completedSalesInRange($yearStart, Carbon::now());
            $lastYearSales = $this->completedSalesInRange($lastYearStart, $lastYearEnd);

            $todayTransactions = (int) SaleDocument::where('status', 'completed')
                ->whereBetween('created_at', [$todayStart, $tomorrowStart])
                ->count();

            $todayItems = (int) Sale::whereBetween('created_at', [$todayStart, $tomorrowStart])
                ->sum('quantity');

            $paymentMethods = SaleDocument::selectRaw('payment_method, COUNT(*) as count, SUM(total_price) as total')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$todayStart, $tomorrowStart])
                ->groupBy('payment_method')
                ->get();

            return [
                'kpi' => [
                    'todays_sales' => $this->kpiPayload($todaySales, $yesterdaySales),
                    'month_sales' => $this->kpiPayload($monthSales, $lastMonthSales),
                    'year_sales' => $this->kpiPayload($yearSales, $lastYearSales),
                    'todays_transactions' => $todayTransactions,
                    'todays_items' => $todayItems,
                ],
                'chart' => $this->chartPayload('1month'),
                'payment_methods' => $paymentMethods,
                'todays_sales' => $todaySales,
                'top_products' => $this->topProducts(),
                'low_stock' => $this->lowStockProducts(),
                'category_stats' => $this->categoryStats(),
            ];
        });

        return response()->json(array_merge(['success' => true], $summary));
    }

    // KPI: Total Penjualan Hari Ini dengan Perubahan
    public function getKPITodaysSales()
    {
        $todayStart = Carbon::today();
        $tomorrowStart = $todayStart->copy()->addDay();
        $yesterdayStart = $todayStart->copy()->subDay();

        $current = $this->completedSalesInRange($todayStart, $tomorrowStart);
        $previous = $this->completedSalesInRange($yesterdayStart, $todayStart);

        return response()->json(array_merge(['success' => true], $this->kpiPayload($current, $previous)));
    }

    // KPI: Total Penjualan Bulan Ini dengan Perubahan
    public function getKPIMonthSales()
    {
        $current = $this->completedSalesInRange(Carbon::now()->startOfMonth(), Carbon::now());
        $previous = $this->completedSalesInRange(Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth());

        return response()->json(array_merge(['success' => true], $this->kpiPayload($current, $previous)));
    }

    // KPI: Total Penjualan Tahun Ini dengan Perubahan
    public function getKPIYearSales()
    {
        $current = $this->completedSalesInRange(Carbon::now()->startOfYear(), Carbon::now());
        $previous = $this->completedSalesInRange(Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear());

        return response()->json(array_merge(['success' => true], $this->kpiPayload($current, $previous)));
    }

    // KPI: Total Transaksi Hari Ini
    public function getKPITodaysTransactions()
    {
        $todayStart = Carbon::today();
        $tomorrowStart = $todayStart->copy()->addDay();

        $count = (int) SaleDocument::where('status', 'completed')
            ->whereBetween('created_at', [$todayStart, $tomorrowStart])
            ->count();

        return response()->json([
            'success' => true,
            'value' => $count,
        ]);
    }

    // KPI: Total Item Terjual Hari Ini
    public function getKPITodaysItems()
    {
        $todayStart = Carbon::today();
        $tomorrowStart = $todayStart->copy()->addDay();

        $items = (int) Sale::whereBetween('created_at', [$todayStart, $tomorrowStart])
            ->sum('quantity');

        return response()->json([
            'success' => true,
            'value' => $items,
        ]);
    }

    // Data: Produk Terlaris (All Time Top 5) — di-cache karena aggregat seluruh riwayat
    public function getTopProducts()
    {
        return response()->json([
            'success' => true,
            'data' => $this->topProducts(),
        ]);
    }

    // Data: Produk Stok Terbatas (< 5)
    public function getLowStockProducts()
    {
        return response()->json([
            'success' => true,
            'data' => $this->lowStockProducts(),
        ]);
    }

    // Data: Penjualan Chart by Range
    public function getSalesChartByRange(Request $request)
    {
        $range = $request->get('range', '1month');

        return response()->json([
            'success' => true,
            'data' => $this->chartPayload($range),
        ]);
    }

    // Data: Payment Methods Today
    public function getPaymentMethods()
    {
        $todayStart = Carbon::today();
        $tomorrowStart = $todayStart->copy()->addDay();

        $paymentMethods = SaleDocument::selectRaw('payment_method, COUNT(*) as count, SUM(total_price) as total')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$todayStart, $tomorrowStart])
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paymentMethods,
            'todaysSales' => $paymentMethods->sum('total'),
        ]);
    }

    // Data: Penjualan per Kategori Hari Ini
    public function getCategoryStats()
    {
        return response()->json([
            'success' => true,
            'data' => $this->categoryStats(),
        ]);
    }

    private function completedSalesInRange(Carbon $from, Carbon $to): int
    {
        return (int) SaleDocument::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_price');
    }

    private function changePercent(int $current, int $previous): ?float
    {
        if ($previous == 0) {
            // Tidak ada baseline pembanding → biarkan UI menampilkan status netral
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function kpiPayload(int $current, int $previous): array
    {
        $change = $this->changePercent($current, $previous);

        return [
            'value' => $current,
            'formatted' => 'Rp ' . number_format($current, 0, ',', '.'),
            'change_percent' => $change,
            'is_increase' => $change !== null ? $change >= 0 : null,
        ];
    }

    private function topProducts()
    {
        return Cache::remember('dashboard.top_products', self::TOP_PRODUCTS_CACHE_TTL, function () {
            return Sale::selectRaw('product_id, SUM(quantity) as total_qty, product_name')
                ->groupBy('product_id', 'product_name')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get();
        });
    }

    private function lowStockProducts()
    {
        return Product::with('category')
            ->where('stock', '<', 5)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();
    }

    private function categoryStats()
    {
        $todayStart = Carbon::today();
        $tomorrowStart = $todayStart->copy()->addDay();

        return Sale::selectRaw('products.category_id, categories.name, COUNT(*) as count, SUM(quantity) as total_qty')
            ->join('products', 'sales.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.created_at', [$todayStart, $tomorrowStart])
            ->groupBy('products.category_id', 'categories.name')
            ->orderByDesc('total_qty')
            ->get();
    }

    private function chartPayload(string $range): array
    {
        if ($range === 'week') {
            // Last 7 Days - Daily breakdown
            $startDate = Carbon::now()->subDays(7);
            $endDate = Carbon::now()->endOfDay();

            $salesData = SaleDocument::selectRaw('DATE(created_at) as date, SUM(total_price) as total')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $allDates = collect();
            for ($i = 7; $i >= 0; $i--) {
                $allDates->push(Carbon::now()->subDays($i)->format('Y-m-d'));
            }

            $dateMap = $salesData->keyBy('date')->toArray();
            $salesArray = $allDates->map(function ($date) use ($dateMap) {
                return $dateMap[$date]['total'] ?? 0;
            })->toArray();

            return [
                'dates' => $allDates->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray(),
                'sales' => $salesArray,
                'range' => 'Minggu Terakhir',
            ];
        }

        if ($range === '3months' || $range === '6months') {
            $monthsBack = $range === '3months' ? 2 : 5;
            $label = $range === '3months' ? '3 Bulan Terakhir' : '6 Bulan Terakhir';
        } else {
            $monthsBack = null;
            $label = 'Bulan Ini';
        }

        $startDate = $monthsBack !== null
            ? Carbon::now()->subMonths($monthsBack)->startOfMonth()
            : Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if ($monthsBack !== null) {
            $salesData = SaleDocument::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_price) as total')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            return [
                'dates' => $salesData->map(fn ($item) => Carbon::parse($item->month)->format('M y'))->toArray(),
                'sales' => $salesData->pluck('total')->toArray(),
                'range' => $label,
            ];
        }

        $salesData = SaleDocument::selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'dates' => $salesData->pluck('date')->map(fn ($d) => Carbon::parse($d)->format('d'))->toArray(),
            'sales' => $salesData->pluck('total')->toArray(),
            'range' => $label,
        ];
    }
}