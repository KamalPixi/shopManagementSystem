<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Warranty;
use App\Models\PaymentMethod;
use App\Services\AuditLogger;
use App\Services\VariationGenerator;
use Illuminate\Validation\Rule;

class StockEdit extends Component
{
    public $product_id;
    public $product;

    public $productAttributes = [
        ['name' => '','values' => '']
    ];

    public $name;
    public $description;
    public $category_id = null;
    public $brand_id = null;
    public $warranty_id = null;
    public $model_no;
    public $serial_no;
    public $sku;
    public $price;
    public $purchase_price;
    public $quantity = 1;
    public $remark;
    public $status = 1;
    public $supplier_id = null;
    public $purchase_date;
    public $payment_method_id;
    public $payment_reference;
    public $payment_status = 'unpaid';
    public $paid_amount = 0;

    // Enhancements
    public $is_vatable = 1;
    public $low_stock_threshold = null;
    public $has_variations = false;
    public $variations = [];

    public function rules() {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'warranty_id' => 'nullable|integer|exists:warranties,id',
            'model_no' => 'nullable|string|max:100',
            'serial_no' => 'nullable|string|max:100',
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($this->product->id),
            ],
            'price' => $this->has_variations ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'quantity' => $this->has_variations ? 'nullable|numeric|min:0' : 'required|numeric|min:0',
            'is_vatable' => 'boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'has_variations' => 'boolean',
            'remark' => 'nullable|string|max:255',
            'status' => 'nullable|numeric|in:0,1',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'purchase_date' => 'nullable|date_format:Y-m-d',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_reference' => 'nullable|string|max:255',
            'payment_status' => 'required|in:unpaid,partial,paid',
            'paid_amount' => 'nullable|numeric|min:0',
            'productAttributes' => 'nullable|array',
            'productAttributes.*' => 'nullable|array',
            'productAttributes.*.name' => 'nullable|string|max:100',
            'productAttributes.*.values' => 'required_with:productAttributes.*.name|string|max:255',
        ];

        if ($this->has_variations) {
            $rules['variations'] = 'required|array|min:1';
            $rules['variations.*.variation_name'] = 'required|string|max:255';
            $rules['variations.*.sku'] = 'nullable|string|max:100';
            $rules['variations.*.serial_no'] = 'nullable|string|max:100';
            $rules['variations.*.price'] = 'required|numeric|min:0';
            $rules['variations.*.purchase_price'] = 'nullable|numeric|min:0';
            $rules['variations.*.quantity'] = 'required|integer|min:0';
            $rules['variations.*.low_stock_threshold'] = 'nullable|integer|min:0';
        }

        return $rules;
    }

    public function mount($product_id) {
        $this->product_id = $product_id;
        $this->product = Product::with('variations')->findOrFail($product_id);
        $this->fill($this->product->toArray());

        $this->is_vatable = $this->product->is_vatable ? 1 : 0;
        $this->low_stock_threshold = $this->product->low_stock_threshold;
        $this->has_variations = (bool) $this->product->has_variations;

        $this->productAttributes = collect($this->product->attributes_and_values)->map(fn($attVal) => [
            'name' => $attVal['name'] ?? '',
            'values' => is_array($attVal['values'] ?? null) ? implode(',', $attVal['values']) : '',
        ])->toArray();

        $this->status = $this->product->status == 'Active' ? 1 : 0;
        $latestPurchase = $this->product->productPurchases()->orderBy('created_at', 'desc')->first();
        $this->supplier_id = $latestPurchase?->supplier_id;
        $this->purchase_date = $latestPurchase?->purchase_date?->format('Y-m-d');
        $this->payment_method_id = $latestPurchase?->payment_method_id;
        $this->payment_reference = $latestPurchase?->payment_reference;
        $this->payment_status = $latestPurchase?->payment_status ?: 'unpaid';
        $this->paid_amount = $latestPurchase?->paid_amount ?: 0;

        // Load variations
        $this->variations = $this->product->variations->map(fn($v) => [
            'id' => $v->id,
            'variation_name' => $v->variation_name,
            'attributes_and_values' => $v->attributes_and_values ?? [],
            'sku' => $v->sku,
            'serial_no' => $v->serial_no,
            'price' => $v->price,
            'purchase_price' => $v->purchase_price,
            'quantity' => $v->quantity,
            'low_stock_threshold' => $v->low_stock_threshold,
        ])->toArray();
    }

    public function addVariationField()
    {
        $this->variations[] = [
            'id' => null,
            'variation_name' => '',
            'sku' => '',
            'serial_no' => '',
            'price' => $this->price ?: 0,
            'purchase_price' => $this->purchase_price ?: 0,
            'quantity' => 1,
            'low_stock_threshold' => $this->low_stock_threshold,
        ];
    }

    public function deleteVariationField($index)
    {
        $varId = $this->variations[$index]['id'] ?? null;
        if ($varId) {
            ProductVariation::where('id', $varId)->where('product_id', $this->product->id)->delete();
        }
        unset($this->variations[$index]);
        $this->variations = array_values($this->variations);
    }

    /**
     * Option B: Generate / Combine variations automatically from entered attributes
     */
    public function generateVariationsFromAttributes()
    {
        $generatedDefinitions = VariationGenerator::generate($this->productAttributes);
        if (empty($generatedDefinitions)) {
            session()->flash('variation_error', __('Please enter at least one attribute with values (e.g. Color: Black, Blue) before generating variations.'));
            return;
        }

        $existingMap = [];
        foreach ($this->variations as $variation) {
            $attributes = $variation['attributes_and_values'] ?? [];
            $key = $attributes ? implode('|', array_map(fn ($pair) => strtolower(trim($pair['name'] ?? '')) . '=' . strtolower(trim($pair['value'] ?? '')), $attributes)) : strtolower(trim($variation['variation_name'] ?? ''));
            if ($key !== '') {
                $existingMap[$key] = $variation;
                if (!empty($variation['variation_name'])) $existingMap['name:' . strtolower(trim($variation['variation_name']))] = $variation;
            }
        }

        $generated = [];
        $baseSku = trim((string) ($this->sku ?? ''));
        foreach ($generatedDefinitions as $index => $definition) {
            $existing = $existingMap[$definition['key']] ?? $existingMap['name:' . strtolower($definition['name'])] ?? null;
            if (!$existing) {
                $suffix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $definition['name']), 0, 12));
                $existing = [
                    'id' => null, 'variation_name' => $definition['name'],
                    'sku' => $baseSku ? $baseSku . '-' . ($suffix ?: ($index + 1)) : '',
                    'serial_no' => '', 'price' => $this->price ?: 0,
                    'purchase_price' => $this->purchase_price ?: 0, 'quantity' => 1,
                    'low_stock_threshold' => $this->low_stock_threshold,
                ];
            }
            $existing['variation_name'] = $definition['name'];
            $existing['attributes_and_values'] = $definition['attributes'];
            $generated[] = $existing;
        }

        $this->variations = $generated;
        session()->flash('variation_success', __(':count variations generated from attributes.', ['count' => count($generated)]));
    }

    public function clearVariations()
    {
        foreach ($this->variations as $var) {
            if (!empty($var['id'])) {
                ProductVariation::where('id', $var['id'])->where('product_id', $this->product->id)->delete();
            }
        }
        $this->variations = [];
    }

    public function update() {
        $validated = $this->validate();

        $attributeArray = collect($this->productAttributes)->map(function ($att) {
            $values = collect(explode(',', strtolower($att['values'] ?? '')))
            ->filter(fn($v) => !empty($v))
            ->toArray();

            return [
                'name' => strtolower($att['name'] ?? ''),
                'values' => $values,
            ];
        })->filter(fn($a) => !empty($a['name']))->values()->toArray();
        
        if ($this->has_variations && count($this->variations) > 0) {
            $validated['quantity'] = collect($this->variations)->sum('quantity');
        }

        $validated['attributes_and_values'] = $attributeArray;
        $validated['updated_by'] = auth()->id();
        $validated['is_vatable'] = filter_var($validated['is_vatable'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $validated['has_variations'] = (bool) $this->has_variations;
        $validated['low_stock_threshold'] = !is_null($this->low_stock_threshold) && $this->low_stock_threshold !== '' ? (int) $this->low_stock_threshold : null;

        $this->product->update($validated);

        // Sync variations
        if ($this->has_variations && count($this->variations) > 0) {
            $keptIds = [];
            foreach ($this->variations as $var) {
                if (!empty($var['id'])) {
                    $existing = ProductVariation::find($var['id']);
                    if ($existing) {
                        $existing->update([
                            'variation_name' => trim($var['variation_name']),
                    'attributes_and_values' => $var['attributes_and_values'] ?? [],
                            'sku' => !empty($var['sku']) ? trim($var['sku']) : null,
                            'serial_no' => !empty($var['serial_no']) ? trim($var['serial_no']) : null,
                            'price' => (float) $var['price'],
                            'purchase_price' => (float) ($var['purchase_price'] ?? 0),
                            'quantity' => (int) $var['quantity'],
                            'low_stock_threshold' => !empty($var['low_stock_threshold']) ? (int) $var['low_stock_threshold'] : null,
                        ]);
                        $keptIds[] = $existing->id;
                    }
                } else {
                    $newVar = $this->product->variations()->create([
                        'variation_name' => trim($var['variation_name']),
                    'attributes_and_values' => $var['attributes_and_values'] ?? [],
                        'sku' => !empty($var['sku']) ? trim($var['sku']) : null,
                        'serial_no' => !empty($var['serial_no']) ? trim($var['serial_no']) : null,
                        'price' => (float) $var['price'],
                        'purchase_price' => (float) ($var['purchase_price'] ?? 0),
                        'quantity' => (int) $var['quantity'],
                        'low_stock_threshold' => !empty($var['low_stock_threshold']) ? (int) $var['low_stock_threshold'] : null,
                        'created_by' => auth()->id(),
                    ]);
                    $keptIds[] = $newVar->id;
                }
            }
            // Remove deleted variations
            $this->product->variations()->whereNotIn('id', $keptIds)->delete();
        }

        $productPurchase = $this->product->productPurchases()->orderBy('created_at', 'desc')->first();
        if ($productPurchase) {
            $productPurchase->update([
                'supplier_id' => !empty($validated['supplier_id']) ? $validated['supplier_id'] : null,
                'purchase_date' => !empty($validated['purchase_date']) ? $validated['purchase_date'] : null,
                'purchase_price' => !empty($validated['purchase_price']) ? $validated['purchase_price'] : 0,
                'sale_price' => $validated['price'],
                'quantity' => $validated['quantity'],
                'attributes_and_values' => $attributeArray,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'payment_reference' => $validated['payment_reference'] ?? null,
                'payment_status' => $validated['payment_status'] ?? 'unpaid',
                'paid_amount' => $validated['paid_amount'] ?? 0,
            ]);
        }

        AuditLogger::log(
            'updated_product',
            __(':user updated product [:name] (SKU: :sku, Qty: :qty, VAT: :vat)', [
                'user' => auth()->user()->name,
                'name' => $this->product->name,
                'sku' => $this->product->sku,
                'qty' => $this->product->quantity,
                'vat' => $this->product->is_vatable ? 'Yes' : 'No'
            ]),
            $this->product,
            ['price' => $this->product->price, 'quantity' => $this->product->quantity]
        );

        session()->flash('success', __('Product updated successfully.'));
    }
    
    public function addAttributeField() {
        $this->productAttributes[] = [
            'name' => '',
            'values' => '',
        ];
    }

    public function deleteAttributeField($index) {
        unset($this->productAttributes[$index]);
        $this->productAttributes = array_values($this->productAttributes);
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('stock:update')) {
            return view('includes.unauthorized');
        }

        return view('livewire.stocks.stock-edit', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'suppliers' => Supplier::all(),
            'warranties' => Warranty::all(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
