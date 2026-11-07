<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashRegisterSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_closing_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'cash_register_session_id');
    }

    public function moneyMovements()
    {
        return $this->hasMany(MoneyMovement::class, 'cash_register_session_id');
    }

    public static function currentOpenSession($userId = null)
    {
        $query = self::where('status', 'open');
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->latest('opened_at')->first();
    }

    /**
     * Check if the currently open session was opened on a previous date (unclosed past day session)
     */
    public static function previousDayUnclosedSession($userId = null): ?self
    {
        $session = self::currentOpenSession($userId);
        if ($session && $session->opened_at->startOfDay()->lt(now()->startOfDay())) {
            return $session;
        }
        return null;
    }

    /**
     * Check if there is an active session opened today
     */
    public static function todayOpenSession($userId = null): ?self
    {
        $session = self::currentOpenSession($userId);
        if ($session && $session->opened_at->isToday()) {
            return $session;
        }
        return null;
    }

    /**
     * Calculate current expected cash in drawer
     */
    public function calculateExpectedCash(): float
    {
        $opening = (float) $this->opening_balance;

        // Cash sales in this session (including cash portion of split payments)
        $saleIds = $this->sales()->pluck('id');
        $hasPayments = SalePayment::whereIn('sale_id', $saleIds)->exists();

        if ($hasPayments) {
            $cashSales = (float) SalePayment::whereIn('sale_id', $saleIds)
                ->where('payment_method', 'cash')
                ->sum('amount');
        } else {
            $cashSales = (float) $this->sales()
                ->where('payment_method', 'cash')
                ->sum('total_price');
        }

        // Cash In movements
        $cashIn = (float) $this->moneyMovements()
            ->where('type', 'in')
            ->sum('amount');

        // Cash Out movements
        $cashOut = (float) $this->moneyMovements()
            ->where('type', 'out')
            ->sum('amount');

        return round($opening + $cashSales + $cashIn - $cashOut, 2);
    }

    /**
     * Calculate payment method breakdown for all sales in this session
     */
    public function calculatePaymentBreakdown(): array
    {
        $saleIds = $this->sales()->pluck('id');

        $breakdown = [
            'cash' => 0.0,
            'card' => 0.0,
            'bank_transfer' => 0.0,
            'other' => 0.0,
            'total_sales' => 0.0,
            'non_cash_total' => 0.0,
        ];

        if ($saleIds->isEmpty()) {
            return $breakdown;
        }

        $payments = SalePayment::whereIn('sale_id', $saleIds)->get();

        if ($payments->isNotEmpty()) {
            foreach ($payments as $payment) {
                $method = strtolower($payment->payment_method ?: 'cash');
                $amt = (float) $payment->amount;

                if (!isset($breakdown[$method])) {
                    $breakdown[$method] = 0.0;
                }
                $breakdown[$method] += $amt;
                $breakdown['total_sales'] += $amt;
                if ($method !== 'cash') {
                    $breakdown['non_cash_total'] += $amt;
                }
            }
        } else {
            $sales = $this->sales()->get();
            foreach ($sales as $sale) {
                $method = strtolower($sale->payment_method ?: 'cash');
                $amt = (float) $sale->total_price;
                if (!isset($breakdown[$method])) {
                    $breakdown[$method] = 0.0;
                }
                $breakdown[$method] += $amt;
                $breakdown['total_sales'] += $amt;
                if ($method !== 'cash') {
                    $breakdown['non_cash_total'] += $amt;
                }
            }
        }

        foreach ($breakdown as $key => $val) {
            $breakdown[$key] = round($val, 2);
        }

        return $breakdown;
    }
}
