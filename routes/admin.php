<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmballageController;
use App\Http\Controllers\Admin\FlourStockController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\MillingController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RawMaterialStockController;
use App\Http\Controllers\Admin\RoastingController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\Settings\CatalogSettingsController;
use App\Http\Controllers\Admin\Settings\PackagingCatalogController;
use App\Http\Controllers\Admin\Settings\ProductCatalogController;
use App\Http\Controllers\Admin\SortingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        Route::resource('employees', EmployeeController::class);
        Route::resource('clients', ClientController::class);

        Route::resource('raw-material-stocks', RawMaterialStockController::class);
        Route::resource('roastings', RoastingController::class);
        Route::resource('sortings', SortingController::class);
        Route::resource('millings', MillingController::class);
        Route::resource('emballages', EmballageController::class);
        Route::get('flour-stock', [FlourStockController::class, 'index'])->name('flour-stock.index');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::resource('sales', SaleController::class);
        Route::resource('users', UserController::class);

        Route::resource('products', ProductController::class);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [CatalogSettingsController::class, 'index'])->name('index');
            Route::resource('product-catalog', ProductCatalogController::class);
            Route::resource('packaging-catalog', PackagingCatalogController::class);
        });
    });
});
