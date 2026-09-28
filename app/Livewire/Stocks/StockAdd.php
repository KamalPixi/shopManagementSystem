<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Warranty;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\AuditLogger;
use App\Services\VariationGenerator;

class StockAdd extends Component
{
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

    // New requested fields
    public $is_vatable = 1; // 1 = VATable, 0 = Non-VATable
    public $low_stock_threshold = null; // null uses shop default
    public $has_variations = false;
    public $variations = [];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'warranty_id' => 'nullable|integer|exists:warranties,id',
            'model_no' => 'nullable|string|max:100',
            'serial_no' => 'nullable|string|max:100',
            'sku' => 'required|string|max:100|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'is_vatable' => 'boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'has_variations' => 'boolean',
            'remark' => 'nullable|string|max:255',
            'status' => 'nullable|numeric|in:0,1',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'purchase_date' => 'nullable|date_format:Y-m-d',
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

    public function mount() {
        $this->purchase_date = date('Y-m-d');
        $this->is_vatable = 1;
    }

    public function addVariationField()
    {
        $this->variations[] = [
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
        unset($this->variations[$index]);
        $this->variations = array_values($this->variations);
    }

    public function updatedHasVariations()
    {
        if ($this->has_variations && empty($this->variations)) {
            // Check if attributes exist to generate from, otherwise add 1 empty row
            $validAttributes = collect($this->productAttributes)->filter(fn($a) => !empty(trim($a['name'] ?? '')) && !empty(trim($a['values'] ?? '')));
            if ($validAttributes->count() > 0) {
                $this->generateVariationsFromAttributes();
            } else {
                $this->addVariationField();
            }
        }
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
        $this->variations = [];
    }

    public function create() {
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
        
        // If variations are present, product total quantity can be sum of variations
        if ($this->has_variations && count($this->variations) > 0) {
            $validated['quantity'] = collect($this->variations)->sum('quantity');
        }

        // Create the product
        $validated['attributes_and_values'] = $attributeArray;
        $validated['created_by'] = auth()->id();
        $validated['is_vatable'] = (bool) $this->is_vatable;
        $validated['has_variations'] = (bool) $this->has_variations;
        $validated['low_stock_threshold'] = !is_null($this->low_stock_threshold) && $this->low_stock_threshold !== '' ? (int) $this->low_stock_threshold : null;

        $product = Product::create($validated);

        // Save variations if enabled
        if ($this->has_variations && count($this->variations) > 0) {
            foreach ($this->variations as $var) {
                $product->variations()->create([
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
            }
        }

        // Create purchase record
        $product->productPurchases()->create([
            'supplier_id' => !empty($validated['supplier_id']) ? $validated['supplier_id'] : null,
            'purchase_date' => !empty($validated['purchase_date']) ? $validated['purchase_date'] : null,
            'purchase_price' => !empty($validated['purchase_price']) ? $validated['purchase_price'] : 0,
            'sale_price' => $validated['price'],
            'quantity' => $validated['quantity'] ?? 0,
            'attributes_and_values' => $attributeArray,
            'created_by' => auth()->id(),
        ]);

        AuditLogger::log(
            'created_product',
            __(':user created new product [:name] (SKU: :sku, Qty: :qty, VAT: :vat)', [
                'user' => auth()->user()->name,
                'name' => $product->name,
                'sku' => $product->sku,
                'qty' => $product->quantity,
                'vat' => $product->is_vatable ? 'Yes' : 'No'
            ]),
            $product,
            ['price' => $product->price, 'quantity' => $product->quantity, 'has_variations' => $this->has_variations]
        );

        $this->reset();
        $this->productAttributes = [
            ['name' => '','values' => '']
        ];
        $this->variations = [];
        $this->is_vatable = 1;
        $this->purchase_date = date('Y-m-d');

        session()->flash('success', __('Product added successfully with inventory records.'));
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
        if (!auth()->user()->hasPermissionTo('stock:create')) {
            return view('includes.unauthorized');
        }

        return view('livewire.stocks.stock-add', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'suppliers' => Supplier::all(),
            'warranties' => Warranty::all(),
        ]);
    }
}
