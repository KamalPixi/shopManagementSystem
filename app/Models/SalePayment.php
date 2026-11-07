<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
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
}
