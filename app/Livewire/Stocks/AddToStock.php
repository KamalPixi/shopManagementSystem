<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Warranty;
use App\Models\Product;
use App\Models\Attribute;

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
            'purchase_price' => 0,
            'quantity' => 0,
            'current_quantity' => $product->quantity,
            'remark' => $product->remark,
            'status' => $product->status,
            'supplier_id' => $product->supplier_id,
            'productAttributesOld' => Collect($product->attributes_and_values)->map(fn($att) => [ 'name' => $att['name'], 'values' => implode(',', $att['values'])])->toArray(),
        ]);
    }
    
    public function create() {
        $validated = $this->validate();

        // dd($validated);

        $product = Product::findBySkuSerialOrModel($this->identifier);

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
            'quantity' => $product->quantity + $validated['quantity'],
            'attributes_and_values' => $attributes,
            'purchase_price' => $validated['purchase_price'],
        ]);

        session()->flash('success', 'Added to stock.');
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
