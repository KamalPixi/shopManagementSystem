<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::namespace('App\Http\Controllers')->group(function () {

    // Auth
    Route::controller(AuthController::class)->group(function () {
        Route::get('/', 'login')->name('login');
        Route::get('/logout', 'logout')->name('logout');
    }); 
        
    // Dashboard
    Route::middleware(['auth'])->group(function () {
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/pos', 'pos')->name('pos');
            Route::get('/sales', 'sales')->name('sales');
            Route::get('/brands', 'brands')->name('brands');
            Route::get('/categories', 'categories')->name('categories');
            Route::get('/warranties', 'warranties')->name('warranties');
            Route::get('/warranty-send', 'warrantySend')->name('warrantySend');
            Route::get('/warranty-receive', 'warrantyReceive')->name('warrantyReceive');
            Route::get('/customer-receive', 'warrantyCustomerReceive')->name('warrantyCustomerReceive');
            Route::get('/stocks', 'stocks')->name('stocks');
            Route::get('/stocks/add', 'stockAdd')->name('stocks.add');
            Route::get('/stocks/add_to_stock', 'addToStock')->name('stocks.addToStock');
            Route::get('/stocks/edit', 'stockEdit')->name('stocks.edit');
            Route::get('/stocks/show', 'stockShow')->name('stocks.show');
            Route::get('/settings', 'settings')->name('settings');
        });
    });

    // Install
    Route::controller(InstallController::class)->group(function () {
        Route::get('/install', 'install')->name('install');
    });
});
