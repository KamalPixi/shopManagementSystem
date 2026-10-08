<?php

namespace App\Livewire\Stocks;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Validation\ValidationException;

trait ValidatesSerialNumbers
{
    public function updated($property)
    {
        if ($property === 'serial_no' || preg_match('/^variations\.[^.]+\.serial_no$/', $property)) {
            $this->validateSerialNumbers();
        }
    }

    protected function validateSerialNumbers(): void
    {
        $this->serial_no = trim((string) $this->serial_no) ?: null;
        $fields = ['serial_no' => $this->serial_no];
        $productId = $this instanceof StockEdit ? $this->product->id : null;

        if ($this->has_variations) {
            foreach ($this->variations as $index => &$variation) {
                $variation['serial_no'] = trim((string) ($variation['serial_no'] ?? '')) ?: null;
                $fields["variations.$index.serial_no"] = $variation['serial_no'];
            }
            unset($variation);
        }

        $errors = [];
        foreach ($fields as $field => $serial) {
            $this->resetValidation($field);
            if ($serial === null) {
                continue;
            }
            $products = Product::whereRaw('LOWER(TRIM(serial_no)) = ?', [mb_strtolower($serial)]);
            $variations = ProductVariation::whereRaw('LOWER(TRIM(serial_no)) = ?', [mb_strtolower($serial)]);
            if ($field === 'serial_no' && $productId) {
                $products->whereKeyNot($productId);
            } elseif ($productId && preg_match('/^variations\.([^.]+)\.serial_no$/', $field, $match)) {
                $id = $this->variations[$match[1]]['id'] ?? null;
                // Only ignore a variation actually owned by the product being edited.
                if ($id && ProductVariation::whereKey($id)->where('product_id', $productId)->exists()) {
                    $variations->whereKeyNot($id);
                }
            }
            $duplicatesInForm = collect($fields)->filter(fn ($value) => $value !== null && mb_strtolower($value) === mb_strtolower($serial))->count() > 1;
            if ($duplicatesInForm || $products->exists() || $variations->exists()) {
                $errors[$field] = __('This serial number is already used by another product or variation.');
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
