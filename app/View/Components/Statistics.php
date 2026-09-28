<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Sale;
use App\Models\Product;
use App\Models\WarrantySend;
use App\Models\CashRegisterSession;
use App\Models\Shop;

class Statistics extends Component
{
    public function __construct()
    {
        //
    }

    public function render(): View|Closure|string
    {
        $shop = Shop::first();
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
        $defaultThreshold = (int) ($shop?->low_stock_threshold ?? 10);

        // Sales Metrics
        $todaySalesCount = Sale::whereDate('created_at', today())->count();
        $todayRevenue = (float) Sale::whereDate('created_at', today())->sum('total_price');
        $todayProfit = (float) Sale::whereDate('created_at', today())->sum('profit');

        $monthRevenue = (float) Sale::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total_price');

        // Cash Register Drawer status for today
        $openSession = CashRegisterSession::todayOpenSession();
        $expectedCash = $openSession ? $openSession->calculateExpectedCash() : 0;
        $overdueSession = CashRegisterSession::previousDayUnclosedSession();

        // Operational Counts
        $lowStockCount = Product::where('status', 1)
            ->whereRaw('quantity <= COALESCE(low_stock_threshold, ?)', [$defaultThreshold])
            ->count();

        $totalProducts = Product::where('status', 1)->count();
        $pendingWarranties = WarrantySend::count();

        return view('components.statistics', [
            'currencySymbol' => $currencySymbol,
            'todaySalesCount' => $todaySalesCount,
            'todayRevenue' => $todayRevenue,
            'todayProfit' => $todayProfit,
            'monthRevenue' => $monthRevenue,
            'openSession' => $openSession,
            'expectedCash' => $expectedCash,
            'overdueSession' => $overdueSession,
            'lowStockCount' => $lowStockCount,
            'totalProducts' => $totalProducts,
            'pendingWarranties' => $pendingWarranties,
        ]);
    }
}
