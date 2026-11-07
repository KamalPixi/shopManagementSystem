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
        'is_vatable' => 'boolean',
        'show_warranty_on_receipt' => 'boolean',
        'show_serial_on_receipt' => 'boolean',
        'attributes_and_values' => 'array',
    ];

    public function product() {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariation() {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function sale() {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function warranty() {
        return $this->belongsTo(Warranty::class, 'warranty_id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getEffectiveCategoryAttribute(): ?Category
    {
        return $this->category ?: $this->product?->category;
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? ($this->product?->category?->name ?? 'Uncategorized');
    }

    public function getNameAttribute()
    {
        return $this->item_name ?: ($this->product?->name ?? 'Item');
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

        $price = (float) ($this->price ?? 0);
        $cost = (float) ($this->purchase_price ?? 0);
        $qty = (int) ($this->quantity ?? 1);
        $discount = (float) ($this->discount ?? 0);

        $grossProfit = (($price - $cost) * $qty) - $discount;

        if ($deductVat && $this->is_vatable && ($gstPercent ?? 0) > 0) {
            $vat = round((float) ($this->total ?? 0) * ($gstPercent / 100), 2);
            return round($grossProfit - $vat, 2);
        }

        return round($grossProfit, 2);
    }
}
