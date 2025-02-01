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

    protected $rules = [
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
        'remark' => 'nullable|string|max:255',
        'status' => 'nullable|numeric|in:0,1',
        'supplier_id' => 'nullable|integer|exists:suppliers,id',
        'purchase_date' => 'nullable|date_format:Y-m-d',
        'productAttributes' => 'nullable|array',
        'productAttributes.*' => 'nullable|array',
        'productAttributes.*.name' => 'nullable|string|max:100',
        'productAttributes.*.values' => 'required_with:productAttributes.*.name|string|max:255',
    ];

    public function mount() {
        $this->purchase_date = date('Y-m-d');
    }

    public function create() {
        $validated = $this->validate();

        $attributeArray = collect($this->productAttributes)->map(function ($att) {
            $values = collect(explode(',', strtolower($att['values'])))
            ->filter(fn($v) => !empty($v))
            ->toArray();

            return [
                'name' => strtolower($att['name']),
                'values' => $values,
            ];
        })->toArray();
        
        // Create the product
        $validated['attributes_and_values'] = $attributeArray;
        $validated['created_by'] = auth()->user()->id;

        $product = Product::create($validated);

        // create purchase record
        $product->productPurchases()->create([
            'supplier_id' => !empty($validated['supplier_id']) ? $validated['supplier_id'] : null,
            'purchase_date' => !empty($validated['purchase_date']) ? $validated['purchase_date'] : null,
            'purchase_price' => !empty($validated['purchase_price']) ? $validated['purchase_price'] : null,
            'sale_price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'attributes_and_values' => $attributeArray,
            'created_by' => auth()->user()->id,

        ]);

        $this->reset();
        $this->productAttribute = [
            ['name' => '','values' => '']
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
