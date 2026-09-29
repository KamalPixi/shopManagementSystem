<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPurchase extends Model
{
    protected $guarded = [];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'purchase_date' => 'date',
        'attributes_and_values' => 'array',
    ];

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }
    
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class);
    }
}
