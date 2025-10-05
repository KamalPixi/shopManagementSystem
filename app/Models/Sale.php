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
            // Get the last number used (starting from 1000)
            $lastNumber = self::max('id') ? (self::max('id') + 999) : 999;

            $nextNumber = $lastNumber + 1; // e.g. 1000, 1001, etc.
            $sale->receipt_no = 'SALE-' . $nextNumber;
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
}
