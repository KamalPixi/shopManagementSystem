<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $guarded = [];
    protected $casts = [
        'total_price' => 'decimal:2',
        'gst' => 'decimal:2',
        'discount' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->receipt_no)) {
                $lastId = self::max('id') ?? 0;
                $nextNumber = $lastId + 1;
                $sale->receipt_no = sprintf('TP-%06d', $nextNumber);
            }
        });
    }

    public function saleItems() {
        return $this->hasMany(SaleItem::class);
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function cashRegisterSession() {
        return $this->belongsTo(CashRegisterSession::class, 'cash_register_session_id');
    }

    public function payments() {
        return $this->hasMany(SalePayment::class);
    }

    public function isSplitPayment(): bool
    {
        if (strtolower($this->payment_method ?? '') === 'split') {
            return true;
        }
        return $this->relationLoaded('payments')
            ? $this->payments->count() > 1
            : $this->payments()->count() > 1;
    }

    public function getPaymentSummaryAttribute(): string
    {
        $payments = $this->relationLoaded('payments') ? $this->payments : $this->payments()->get();
        if ($payments->count() > 1) {
            $currencySymbol = Shop::first()?->currency['symbol'] ?? '€';
            return $payments->map(function ($p) use ($currencySymbol) {
                return $p->method_label . ': ' . $currencySymbol . number_format($p->amount, 2);
            })->implode(' | ');
        }

        $first = $payments->first();
        if ($first) {
            return $first->method_label;
        }

        return match (strtolower($this->payment_method ?: 'cash')) {
            'cash' => __('Cash'),
            'card' => __('Card'),
            'bank_transfer' => __('Bank transfer'),
            'other' => __('Other'),
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    public function getCalculatedProfit(?bool $deductVat = null, ?float $gstPercent = null): float
    {
        if ($deductVat === null) {
            $shop = Shop::first();
            $deductVat = (bool) ($shop?->settings['deduct_vat_from_profit'] ?? false);
            $gstPercent = (float) ($shop?->gst_percent ?? 0);
        } elseif ($gstPercent === null && $deductVat) {
            $gstPercent = (float) (Shop::first()?->gst_percent ?? 0);
        }

        $totalProfit = 0.0;
        foreach ($this->saleItems as $item) {
            $totalProfit += $item->getCalculatedProfit($deductVat, $gstPercent);
        }

        return round($totalProfit, 2);
    }
}
