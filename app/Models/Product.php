<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'created_by',
    ];

    public static function findBySkuSerialOrModel(string $value): ?self
    {
        $lowerValue = strtolower($value); // Convert search term to lowercase
        return self::whereRaw('LOWER(sku) = ?', [$lowerValue])
            ->orWhereRaw('LOWER(serial_no) = ?', [$lowerValue])
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

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function productPurchases() {
        return $this->hasMany(ProductPurchase::class);
    }

    public function saleItems() {
        return $this->hasMany(SaleItem::class, 'product_id');
    }
    
    public function getStatusAttribute()
    {
        return $this->attributes['status'] ? 'Active' : 'Inactive';
    }
}
