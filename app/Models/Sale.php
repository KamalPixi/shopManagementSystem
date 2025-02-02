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
