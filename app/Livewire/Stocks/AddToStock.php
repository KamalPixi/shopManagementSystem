<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Warranty;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\ProductVariation;

class AddToStock extends Component
{
    public $productAttributes = [
        ['name' => '','values' => '']
    ];
    public $productAttributesOld = [
        ['name' => '','values' => '']
    ];

    public $identifier;

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
    public $current_quantity;
    public $quantity;
    public $remark;
    public $status = 1;
    public $supplier_id = null;
    public $purchase_date;

    // helper property
    public $attribute_readonly_index = 0;
    public $has_variations = false;
    public $selected_variation_id = null;
    public $variations = [];

    protected $rules = [
        'purchase_price' => 'required|numeric|min:0',
        'quantity' => 'required|numeric|min:0',
        'supplier_id' => 'nullable|integer|exists:suppliers,id',
        'purchase_date' => 'required|date_format:Y-m-d',
        'productAttributes' => 'nullable|array',
        'productAttributes.*' => 'nullable|array',
        'productAttributes.*.name' => 'nullable|string|max:100',
        'productAttributes.*.values' => 'required_with:productAttributes.*.name|string|max:255',
    ];

    protected $messages = [
        'productAttributes.*.values.required_with' => 'The values field is required when the attribute name is provided.',
        'productAttributes.*.values.*.string' => 'Each value must be a valid string.',
        'productAttributes.*.name.string' => 'The attribute name must be a valid string.',
    ];

    public function mount() {
        $this->purchase_date = date('Y-m-d');
    }

    public function findProduct() {
        $product = Product::findBySkuSerialOrModel($this->identifier);
        if (!$product) {
            return session()->flash('success', 'Not found!');
        }

        $this->has_variations = (bool) $product->has_variations;
        $this->variations = $product->variations()->orderBy('variation_name')->get()->map(function ($variation) {
            return array_merge($variation->toArray(), ['add_quantity' => 0, 'add_purchase_price' => $variation->purchase_price]);
        })->toArray();
        $this->selected_variation_id = $product->matchedVariation?->id;
        $latestPurchase = $product->productPurchases()->latest('created_at')->first();

        $this->fill([
            'name' => $product->name,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'warranty_id' => $product->warranty_id,
            'model_no' => $product->model_no,
            'serial_no' => $product->serial_no,
            'sku' => $product->sku,
            'price' => $product->price,
            'purchase_price' => $latestPurchase?->purchase_price ?? 0,
            'quantity' => 0,
            'current_quantity' => $product->quantity,
            'remark' => $product->remark,
            'status' => $product->status,
            'supplier_id' => $latestPurchase?->supplier_id ?? $product->supplier_id,
            'purchase_date' => $latestPurchase?->purchase_date?->format('Y-m-d') ?? date('Y-m-d'),
            'productAttributesOld' => Collect($product->attributes_and_values)->map(fn($att) => [ 'name' => $att['name'], 'values' => implode(',', $att['values'])])->toArray(),
        ]);

        if ($product->matchedVariation) {
            $this->price = $product->matchedVariation->price;
            $this->current_quantity = $product->matchedVariation->quantity;
        }
    }
    
    public function updatedSelectedVariationId($variationId)
    {
        $variation = collect($this->variations)->firstWhere('id', (int) $variationId);
        if ($variation) {
            $this->price = $variation['price'];
            $this->current_quantity = $variation['quantity'];
        }
    }

    public function create() {
        $validated = $this->validate();

        // dd($validated);

        $product = Product::findBySkuSerialOrModel($this->identifier);
        $variation = null;
        $bulkQuantity = 0;
        if ($this->has_variations) {
            $bulkRows = collect($this->variations)->filter(fn ($row) => (int) ($row['add_quantity'] ?? 0) > 0);
            if ($bulkRows->isEmpty()) {
                $this->addError('variations', __('Enter a quantity for at least one variation.'));
                return;
            }
            foreach ($bulkRows as $row) {
                $dbVariation = ProductVariation::where('product_id', $product->id)->find($row['id'] ?? null);
                if (!$dbVariation) continue;
                $qty = (int) $row['add_quantity'];
                $dbVariation->increment('quantity', $qty);
                if (isset($row['add_purchase_price']) && $row['add_purchase_price'] !== '') {
                    $dbVariation->update(['purchase_price' => (float) $row['add_purchase_price']]);
                }
                $bulkQuantity += $qty;
            }
            if ($bulkQuantity < 1) {
                $this->addError('variations', __('Enter a valid quantity for at least one variation.'));
                return;
            }
            $validated['quantity'] = $bulkQuantity;
        }

        $newAttributes = collect($this->productAttributes)->map(function ($att) {
            $values = collect(explode(',', strtolower($att['values'])))
            ->filter(fn($v) => !empty($v))
            ->toArray();

            return [
                'name' => strtolower($att['name']),
                'values' => $values,
            ];
        })->toArray();

        // create product purchase record
        $product->productPurchases()->create([
            'supplier_id' => !empty($validated['supplier_id']) ? $validated['supplier_id'] : null,
            'purchase_date' => !empty($validated['purchase_date']) ? $validated['purchase_date'] : null,
            'purchase_price' => !empty($validated['purchase_price']) ? $validated['purchase_price'] : 0,
            'quantity' => $validated['quantity'],
            'attributes_and_values' => $newAttributes,
            'created_by' => auth()->user()->id,
        ]);

        // merge attributes
        $attributes = collect($product->attributes_and_values);
        foreach ($newAttributes as $newAttribute) {
            // Check if the attribute already exists
            $existingAttribute = $attributes->firstWhere('name', $newAttribute['name']);
        
            if ($existingAttribute) {
                // If the attribute exists, add missing values
                $attributes = $attributes->map(function ($attribute) use ($newAttribute) {
                    if ($attribute['name'] === $newAttribute['name']) {
                        // Merge values, remove duplicates, and re-index
                        $attribute['values'] = collect($attribute['values'])
                            ->merge($newAttribute['values'])
                            ->unique()
                            ->values()
                            ->toArray();
                    }
                    return $attribute;
                });
            } else {
                // If the attribute doesn't exist, add it
                $attributes->push($newAttribute);
            }
        }  
        $attributes = $attributes->filter(function ($attribute) {
            return !empty($attribute['name']); // Keep attributes with non-empty 'name'
        })->values()->toArray();   

        $product->update([
            'quantity' => $product->quantity + ($this->has_variations ? $bulkQuantity : $validated['quantity']),
            'attributes_and_values' => $attributes,
            'purchase_price' => $validated['purchase_price'],
            'updated_by' => auth()->id(),
        ]);

        \App\Services\AuditLogger::log(
            'added_to_stock',
            __(':user added :qty units to stock for [:name] (SKU: :sku)', [
                'user' => auth()->user()->name,
                'qty' => $validated['quantity'],
                'name' => $product->name,
                'sku' => $product->sku
            ]),
            $product,
            ['added_qty' => $validated['quantity'], 'new_total_qty' => $product->quantity]
        );

        session()->flash('success', __('Added to stock.'));
    }
    


    public function addAttributeField() {
        $this->productAttributes[] = [
            'name' => '',
            'value' => '',
        ];
    }
    public function deleteAttributeField($index) {
        unset($this->productAttributes[$index]);
        $this->productAttributes = array_values($this->productAttributes);
    }

    public function addAttribute() {
        
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('stock:create')) {
            return view('includes.unauthorized');
        }

        return view('livewire.stocks.add-to-stock', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'suppliers' => Supplier::all(),
            'warranties' => Warranty::all(),
        ]);
    }
}
