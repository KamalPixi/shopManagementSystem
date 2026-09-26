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
}
