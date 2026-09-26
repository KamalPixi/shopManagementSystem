<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyMovement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(MoneyCategory::class, 'money_category_id');
    }

    public function registerSession()
    {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }
}
