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
     * Calculate current expected cash in drawer
     */
    public function calculateExpectedCash(): float
    {
        $opening = (float) $this->opening_balance;

        // Cash sales in this session
        $cashSales = (float) $this->sales()
            ->where('payment_method', 'cash')
            ->sum('total_price');

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
}
