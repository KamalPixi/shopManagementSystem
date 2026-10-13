<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'is_vatable' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    /**
     * Search custom items matching keyword, ordered by usage count and recency.
     */
    public static function searchSuggestions(string $keyword, int $limit = 8)
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return static::query()
                ->orderByDesc('usage_count')
                ->orderByDesc('last_used_at')
                ->limit($limit)
                ->get();
        }

        return static::query()
            ->where('name', 'like', '%' . $keyword . '%')
            ->orderByRaw("CASE WHEN LOWER(name) LIKE ? THEN 1 WHEN LOWER(name) LIKE ? THEN 2 ELSE 3 END", [
                strtolower($keyword) . '%',
                '%' . strtolower($keyword) . '%',
            ])
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Record or update usage of a custom item.
     */
    public static function recordUsage(
        string $name,
        float $price,
        float $purchasePrice = 0,
        ?int $warrantyId = null,
        ?string $warrantyDescription = null,
        bool $isVatable = true
    ): static {
        $name = trim($name);
        $item = static::firstOrNew(['name' => $name]);

        $item->price = $price;
        $item->purchase_price = $purchasePrice;
        $item->warranty_id = $warrantyId;
        $item->warranty_description = $warrantyDescription;
        $item->is_vatable = $isVatable;
        $item->usage_count = ($item->usage_count ?? 0) + 1;
        $item->last_used_at = now();
        $item->save();

        return $item;
    }
}
