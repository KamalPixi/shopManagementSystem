<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::controller(AuthController::class)->group(function () {
    Route::get('/', 'login')->name('login');
    Route::get('/logout', 'logout')->name('logout');
});

Route::middleware(['auth'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/pos', 'pos')->name('pos');
        Route::get('/sales/index', 'sales')->name('sales.index');
        Route::get('/sales/edit/{sale_id}', 'saleEdit')->name('sales.edit');
        Route::get('/sales/report', 'salesReports')->name('sales.report');
        Route::get('/brands', 'brands')->name('brands');
        Route::get('/categories', 'categories')->name('categories');
        Route::get('/warranties', 'warranties')->name('warranties');
        Route::get('/warranty-send', 'warrantySend')->name('warrantySend');
        Route::get('/warranty-receive', 'warrantyReceive')->name('warrantyReceive');
        Route::get('/customer-receive', 'warrantyCustomerReceive')->name('warrantyCustomerReceive');
        Route::get('/stocks/index', 'stocks')->name('stocks.index');
        Route::get('/stocks/add', 'stockAdd')->name('stocks.add');
        Route::get('/stocks/add_to_stock', 'addToStock')->name('stocks.addToStock');
        Route::get('/stocks/edit/{product_id}', 'stockEdit')->name('stocks.edit');
        Route::get('/stocks/show/{product_id}', 'stockShow')->name('stocks.show');
        Route::get('/settings', 'settings')->name('settings');
        Route::get('/users', 'users')->name('users');
        Route::get('/suppliers', 'suppliers')->name('suppliers');
        Route::get('/customers', 'customers')->name('customers');
        Route::get('/profile', 'profile')->name('profile');
        Route::get('/db-import-export', 'dbImportExport')->name('dbImportExport');
    });
});
