<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes - require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return view('admin.users.index');
    });

    // Dashboard Route - accessible to both admin and kasir
    Route::get('/dashboard', App\Http\Controllers\DashboardController::class . '@index')->name('dashboard');
    
    // Dashboard API Routes
    Route::prefix('api/dashboard')->controller(App\Http\Controllers\DashboardController::class)->group(function () {
        Route::get('/kpi/todays-sales', 'getKPITodaysSales')->name('dashboard.api.kpi.todays-sales');
        Route::get('/kpi/month-sales', 'getKPIMonthSales')->name('dashboard.api.kpi.month-sales');
        Route::get('/kpi/year-sales', 'getKPIYearSales')->name('dashboard.api.kpi.year-sales');
        Route::get('/kpi/todays-transactions', 'getKPITodaysTransactions')->name('dashboard.api.kpi.todays-transactions');
        Route::get('/kpi/todays-items', 'getKPITodaysItems')->name('dashboard.api.kpi.todays-items');
        Route::get('/top-products', 'getTopProducts')->name('dashboard.api.top-products');
        Route::get('/low-stock', 'getLowStockProducts')->name('dashboard.api.low-stock');
        Route::get('/chart/sales', 'getSalesChartByRange')->name('dashboard.api.chart.sales');
        Route::get('/payment-methods', 'getPaymentMethods')->name('dashboard.api.payment-methods');
        Route::get('/category-stats', 'getCategoryStats')->name('dashboard.api.category-stats');
    });

    // Admin Routes - only for admin role
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::resource('categories', App\Http\Controllers\CategoryController::class, [
            'except' => ['create', 'edit', 'show']
        ]);
        Route::resource('products', App\Http\Controllers\ProductController::class, [
            'except' => ['create', 'edit', 'show']
        ]);
        Route::resource('users', App\Http\Controllers\UserController::class, [
            'except' => ['create', 'edit', 'show']
        ]);
    });

    // Sale Routes - accessible to both admin and kasir
    Route::middleware(['role:admin,kasir'])->controller(App\Http\Controllers\SaleController::class)->group(function () {
        Route::get('/admin/sales', 'index')->name('sales.index');
        Route::get('/admin/sales/search', 'search')->name('sales.search');
        Route::post('/admin/sales/add-to-cart', 'addToCart')->name('sales.add-to-cart');
        Route::put('/admin/sales/update-cart', 'updateCart')->name('sales.update-cart');
        Route::delete('/admin/sales/remove-from-cart', 'removeFromCart')->name('sales.remove-from-cart');
        Route::post('/admin/sales/checkout', 'checkout')->name('sales.checkout');
        Route::get('/admin/sales/{saleDocument}', 'show')->name('sales.show');
        Route::get('/admin/sales-history', 'history')->name('sales.history');
    });
});