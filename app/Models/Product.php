<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'attributes_and_values' => 'array',
        'is_vatable' => 'boolean',
        'has_variations' => 'boolean',
        'low_stock_threshold' => 'integer',
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
        'low_stock_threshold',
        'is_vatable',
        'has_variations',
        'attributes_and_values',
        'remark',
        'status',
        'created_by',
        'updated_by',
    ];

    public static function findBySkuSerialOrModel(string $value): ?self
    {
        $lowerValue = strtolower(trim($value));
        $product = self::whereRaw('LOWER(sku) = ?', [$lowerValue])
            ->orWhereRaw('LOWER(serial_no) = ?', [$lowerValue])
            ->orWhereRaw('LOWER(model_no) = ?', [$lowerValue])
            ->first();

        if ($product) {
            return $product;
        }

        // Check product variations
        $variation = ProductVariation::whereRaw('LOWER(sku) = ?', [$lowerValue])
            ->orWhereRaw('LOWER(serial_no) = ?', [$lowerValue])
            ->first();

        if ($variation) {
            $prod = $variation->product;
            if ($prod) {
                $prod->setRelation('matchedVariation', $variation);
                return $prod;
            }
        }

        return null;
    }

    public function variations() {
        return $this->hasMany(ProductVariation::class);
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

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
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

    /**
     * Get effective low stock threshold (falls back to shop setting or default 10)
     */
    public function getEffectiveLowStockThresholdAttribute(): int
    {
        if (!is_null($this->low_stock_threshold)) {
            return (int) $this->low_stock_threshold;
        }
        $shop = Shop::first();
        return (int) ($shop?->low_stock_threshold ?? 10);
    }

    /**
     * Check if product is in low stock condition
     */
    public function getIsLowStockAttribute(): bool
    {
        if ($this->has_variations) {
            $variations = $this->relationLoaded('variations') ? $this->variations : $this->variations()->get();
            if ($variations->isNotEmpty()) {
                $defaultThreshold = $this->effective_low_stock_threshold;
                return $variations->contains(function ($variation) use ($defaultThreshold) {
                    $threshold = is_null($variation->low_stock_threshold) ? $defaultThreshold : (int) $variation->low_stock_threshold;
                    return (int) $variation->quantity <= $threshold;
                });
            }
        }
        return (int) $this->quantity <= $this->effective_low_stock_threshold;
    }
}
