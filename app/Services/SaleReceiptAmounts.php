<?php

namespace App\Services;

use App\Models\Sale;

final class SaleReceiptAmounts
{
    public static function forSale(Sale $sale): array
    {
        $items = $sale->saleItems->values();
        $inclusive = abs((float) $sale->total_price - $items->sum(fn ($item) => (float) $item->total)) < 0.02;
        $taxable = $items->filter(fn ($item) => (bool) $item->is_vatable);
        $taxableTotal = $taxable->sum(fn ($item) => (float) $item->total);
        $share = $taxableTotal > 0 ? (float) $sale->gst / $taxableTotal : 0;
        $lastTaxableIndex = $taxable->keys()->last();
        $allocated = 0;
        $rows = [];

        foreach ($items as $index => $item) {
            $vat = $item->is_vatable ? round((float) $item->total * $share, 2) : 0;
            if ($item->is_vatable && $index === $lastTaxableIndex) {
                $vat = round((float) $sale->gst - $allocated, 2);
            }
            $allocated += $vat;
            $rows[] = [
                'unit_net' => $inclusive && $item->is_vatable ? (float) $item->price * (1 - $share) : (float) $item->price,
                'vat' => $vat,
                'total' => (float) $item->total + ($inclusive ? 0 : $vat),
            ];
        }

        return $rows;
    }
}
