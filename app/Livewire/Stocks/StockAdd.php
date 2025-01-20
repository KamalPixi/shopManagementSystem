<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Warranty;
use App\Models\Product;
use App\Models\Attribute;

class StockAdd extends Component
{
    public $productAttributes = [
        ['name' => '','value' => '']
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
    public $quantity;
    public $remark;
    public $status = 1;
    public $supplier_id = null;
    public $purchase_date;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'category_id' => 'nullable|integer|exists:categories,id',
        'brand_id' => 'nullable|integer|exists:brands,id',
        'warranty_id' => 'nullable|integer|exists:warranties,id',
        'model_no' => 'nullable|string|max:100',
        'serial_no' => 'nullable|string|max:100',
        'sku' => 'nullable|string|max:100',
        'price' => 'required|numeric|min:0',
        'purchase_price' => 'nullable|numeric|min:0',
        'quantity' => 'nullable|numeric|min:0',
        'remark' => 'nullable|string|max:255',
        'status' => 'nullable|numeric|in:0,1',
        'supplier_id' => 'nullable|integer|exists:suppliers,id',
        'purchase_date' => 'nullable|date_format:Y-m-d',
        'productAttributes' => 'nullable|array',
        'productAttributes.*' => 'nullable|array',
        'productAttributes.*.name' => 'nullable|string|max:100',
        'productAttributes.*.value' => 'required_with:productAttributes.*.name|string|max:100',
    ];

    public function mount() {
        $this->purchase_date = date('Y-m-d');
    }

    public function create() {
        $validated = $this->validate();

        // Normalize and map product attributes
        $attributeCollect = collect($this->productAttributes)->map(function ($att) {
            return [
                'name' => strtolower($att['name']),
                'value' => strtolower($att['value']),
            ];
        });
    
        // Batch process attributes and values
        $attributeCollect->each(function ($att) {
            $attribute = Attribute::firstOrCreate(['name' => $att['name']]);
            $attribute->attributeValues()->firstOrCreate(['value' => $att['value']]);
        });
    
        // Preload attribute models with values
        $attributeModels = Attribute::with('attributeValues')
            ->whereIn('name', $attributeCollect->pluck('name')->toArray())
            ->get();
    
        // Create the product
        $product = Product::create($validated);
    
        // Associate attributes and values with the product
        $attributeCollect->each(function ($att) use ($product, $attributeModels) {
            $attribute = $attributeModels->firstWhere('name', $att['name']);
            $attributeValue = $attribute->attributeValues->firstWhere('value', $att['value']);
    
            $product->productAttributes()->create([
                'attribute_id' => $attribute->id,
                'attribute_value_id' => $attributeValue->id,
            ]);
        });

        // purchase data
        $product->productPurchases()->create([
            'supplier_id' => !empty($validated['supplier_id']) ? $validated['supplier_id'] : null,
            'purchase_date' => !empty($validated['purchase_date']) ? $validated['purchase_date'] : null,
            'purchase_price' => !empty($validated['purchase_price']) ? $validated['purchase_price'] : null,
            'sale_price' => $validated['price'],
            'quantity' => $validated['quantity'],
        ]);
        $attributeCollect->each(function ($att) use ($product, $attributeModels) {
            $attribute = $attributeModels->firstWhere('name', $att['name']);
            $attributeValue = $attribute->attributeValues->firstWhere('value', $att['value']);
    
            $product->productPurchaseAttributes()->create([
                'attribute_id' => $attribute->id,
                'attribute_value_id' => $attributeValue->id,
            ]);
        });

        $this->reset();
        $this->productAttribute = [
            ['name' => '','value' => '']
        ];

        session()->flash('success', 'Product added.');
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
        return view('livewire.stocks.stock-add', [
            'categories' => Category::all(),
            'brands' => Brand::all(),
            'suppliers' => Supplier::all(),
            'warranties' => Warranty::all(),
        ]);
    }
}
