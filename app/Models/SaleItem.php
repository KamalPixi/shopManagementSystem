<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public $casts = [
        'purchase_price' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'profit' => 'decimal:2',
        'attributes_and_values' => 'array',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function sale() {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function warranty() {
        return $this->belongsTo(Warranty::class, 'warranty_id');
    }

    public function getNameAttribute()
    {
        return $this->item_name ?: ($this->product?->name ?? 'Item');
    }
}

