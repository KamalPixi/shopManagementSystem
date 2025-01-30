<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\FileService;

class Product extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'attributes_and_values' => 'array',
    ];

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'brand_id',
        'warranty_id',
        'model_no',
        'serial_no',
        'sku',
        'price',
        'purchase_price',
        'quantity',
        'attributes_and_values',
        'remark',
        'status',
    ];

    public static function findBySkuSerialOrModel(string $value): ?self
    {
        return self::where('sku', $value)
            ->orWhere('serial_no', $value)
            ->orWhere('model_no', $value)
            ->first();
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function brand() {
        return $this->belongsTo(Brand::class);
    }

    public function warranty() {
        return $this->belongsTo(Warranty::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function productPurchases() {
        return $this->hasMany(ProductPurchase::class);
    }
    
    public function getStatusAttribute()
    {
        return $this->attributes['status'] ? 'Active' : 'Inactive';
    }
}
