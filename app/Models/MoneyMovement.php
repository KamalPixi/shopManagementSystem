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

    public function cashRegisterSession()
    {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }

    public function getMethodLabelAttribute(): string
    {
        return match (strtolower($this->payment_method ?: 'cash')) {
            'cash' => __('Cash'),
            'card' => __('Card'),
            'bank_transfer' => __('Bank transfer'),
            'other' => __('Other'),
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    public function getMethodBadgeClassAttribute(): string
    {
        return match (strtolower($this->payment_method ?: 'cash')) {
            'cash' => 'bg-success-subtle text-success border border-success-subtle',
            'card' => 'bg-info-subtle text-info border border-info-subtle',
            'bank_transfer' => 'bg-primary-subtle text-primary border border-primary-subtle',
            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
    }

    public function getMethodIconAttribute(): string
    {
        return match (strtolower($this->payment_method ?: 'cash')) {
            'cash' => 'fa-money-bill-wave',
            'card' => 'fa-credit-card',
            'bank_transfer' => 'fa-building-columns',
            default => 'fa-ellipsis',
        };
    }
}
