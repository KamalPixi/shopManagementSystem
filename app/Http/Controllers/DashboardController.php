<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Shop;

class DashboardController extends Controller
{
    public function dashboard() {
        $shop = Shop::first();
        $defaultThreshold = (int) ($shop?->low_stock_threshold ?? 10);

        $products = Product::with('category')
                        ->where('status', 1)
                        ->whereRaw('quantity <= COALESCE(low_stock_threshold, ?)', [$defaultThreshold])
                        ->orderBy('quantity', 'asc')
                        ->paginate(5, ['*'], 'low_stock_page');

        $lowStockCount = Product::where('status', 1)
                        ->whereRaw('quantity <= COALESCE(low_stock_threshold, ?)', [$defaultThreshold])
                        ->count();

        $recentSales = \App\Models\Sale::with(['createdBy', 'saleItems'])
                        ->latest('created_at')
                        ->take(5)
                        ->get();

        $currentSession = \App\Models\CashRegisterSession::currentOpenSession();

        return view('dashboard', compact('products', 'lowStockCount', 'recentSales', 'currentSession', 'shop'));
    }

    public function pos() {
        return view('pos');
    }

    public function brands() {
        return view('brands');
    }

    public function categories() {
        return view('categories');
    }

    public function warranties() {
        return view('warranties');
    }

    public function warrantySend() {
        return view('warranty-send');
    }

    public function warrantyReceive() {
        return view('warranty-receive');
    }

    public function warrantyCustomerReceive() {
        return view('warranty-customer-receive');
    }

    public function stocks() {
        return view('stocks.stocks');
    }

    public function stockAdd() {
        return view('stocks.add');
    }

    public function addToStock() {
        return view('stocks.add-to-stock');
    }

    public function stockEdit($product_id) {
        return view('stocks.edit', compact('product_id'));
    }

    public function stockShow($product_id) {
        return view('stocks.show', compact('product_id'));
    }

    public function sales() {
        return view('sales.sales');
    }

    public function salesReports() {
        return redirect()->route('sales.index');
    }

    public function saleEdit($sale_id) {
        return view('sales.edit', compact('sale_id'));
    }

    public function suppliers() {
        return view('suppliers');
    }

    public function customers() {
        return view('customers');
    }

    public function settings() {
        return view('settings');
    }

    public function users() {
        return view('users');
    }

    public function profile() {
        return view('profile');
    }

    public function dbImportExport() {
        return view('db-import-export');
    }

    public function cashRegister() {
        return view('cash-register');
    }

    public function moneyMovements() {
        return view('money-movements');
    }

    public function auditLogs() {
        return view('audit-logs');
    }
}
