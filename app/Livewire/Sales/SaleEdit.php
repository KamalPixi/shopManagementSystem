<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Warranty;
use App\Models\Shop;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class SaleEdit extends Component
{
    public $sale_id;
    public $sale;

    public $identifier = '';
    public $products = [];
    public $sale_items = [];

    // Track original quantities for stock adjustment: [product_id => total_original_qty]
    public $original_stock_quantities = [];

    public $subtotal = 0;
    public $discount = 0;
    public $gst = 0;
    public $grand_total = 0;

    public $customer_name = '';
    public $customer_phone_no = '';
    public $customers = [];
    public $customer_id;

    public $gst_percent = 0;
    public $errorMessage = '';

    // For adding custom item
    public $showCustomModal = false;
    public $custom_item_name = '';
    public $custom_price = '';
    public $custom_purchase_price = 0;
    public $custom_quantity = 1;
    public $custom_warranty_id = '';
    public $custom_warranty_description = '';

    public function mount($sale_id)
    {
        $this->sale_id = $sale_id;
        $this->sale = Sale::with(['saleItems.product', 'customer'])->findOrFail($sale_id);

        $shop = Shop::first();
        if ($shop) {
            $this->gst_percent = (float) $shop->gst_percent;
        }

        $this->customer_id = $this->sale->customer_id;
        $this->customer_name = $this->sale->customer?->name ?? 'Walk In';
        $this->customer_phone_no = $this->sale->customer?->phone_no ?? '000000000';

        $this->original_stock_quantities = [];

        foreach ($this->sale->saleItems as $item) {
            $itemKey = !empty($item->product_id) ? 'prod_' . $item->product_id : 'custom_' . $item->id;

            if ($item->product) {
                $this->products[] = $item->product;
                $this->original_stock_quantities[$item->product_id] = ($this->original_stock_quantities[$item->product_id] ?? 0) + (int) $item->quantity;
            }

            $this->sale_items[$itemKey] = [
                'key' => $itemKey,
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'item_name' => $item->name,
                'warranty_id' => $item->warranty_id,
                'warranty_description' => $item->warranty_description ?? ($item->warranty?->description ?? ''),
                'price' => (float) $item->price,
                'purchase_price' => (float) $item->purchase_price,
                'discount' => (float) $item->discount,
                'quantity' => (int) $item->quantity,
                'attributes_and_values' => $item->attributes_and_values ?? [],
                'total' => (float) $item->total,
                'is_custom' => empty($item->product_id),
            ];
        }

        $this->calculate();
    }

    public function updated($propertyName)
    {
        $this->errorMessage = '';

        if ($propertyName == 'customer_name') {
            if (empty($this->customer_name)) {
                $this->customer_id = null;
                $this->customers = [];
                return;
            }
            $this->customers = Customer::where('name', 'like', '%' . $this->customer_name . '%')->get();
            return;
        }

        // When custom modal warranty changes, auto-fill description
        if ($propertyName == 'custom_warranty_id') {
            if (!empty($this->custom_warranty_id)) {
                $w = Warranty::find($this->custom_warranty_id);
                $this->custom_warranty_description = $w?->description ?? '';
            } else {
                $this->custom_warranty_description = '';
            }
            return;
        }

        // When warranty_id changes for a cart line item, auto-fill its warranty_description
        if (preg_match('/^sale_items\.(.+)\.warranty_id$/', $propertyName, $matches)) {
            $itemKey = $matches[1];
            if (isset($this->sale_items[$itemKey])) {
                $wid = $this->sale_items[$itemKey]['warranty_id'];
                if (!empty($wid)) {
                    $warranty = Warranty::find($wid);
                    $this->sale_items[$itemKey]['warranty_description'] = $warranty?->description ?? '';
                } else {
                    $this->sale_items[$itemKey]['warranty_description'] = '';
                }
            }
            return;
        }

        // Validate stock on quantity change
        if (preg_match('/^sale_items\.(.+)\.quantity$/', $propertyName, $matches)) {
            $itemKey = $matches[1];
            if (isset($this->sale_items[$itemKey])) {
                $qty = (int) $this->sale_items[$itemKey]['quantity'];
                if ($qty < 1) {
                    $this->sale_items[$itemKey]['quantity'] = 1;
                    $this->errorMessage = 'Quantity must be at least 1.';
                } elseif (!empty($this->sale_items[$itemKey]['product_id'])) {
                    $productId = $this->sale_items[$itemKey]['product_id'];
                    $product = Product::find($productId);

                    if ($product) {
                        $originalQty = $this->original_stock_quantities[$productId] ?? 0;
                        $maxAllowed = $product->quantity + $originalQty;

                        if ($qty > $maxAllowed) {
                            $this->sale_items[$itemKey]['quantity'] = max(1, $maxAllowed);
                            $this->errorMessage = "Cannot exceed available stock! Current stock: {$product->quantity}, already in this sale: {$originalQty} (Max: {$maxAllowed}) for [{$product->name}].";
                        }
                    }
                }
            }
        }

        $this->calculate();
    }

    public function add()
    {
        $product = Product::findBySkuSerialOrModel($this->identifier);

        if (empty($product)) {
            session()->flash('failed', "No product found [{$this->identifier}]");
            return;
        }

        $itemKey = 'prod_' . $product->id;
        $originalQty = $this->original_stock_quantities[$product->id] ?? 0;
        $maxAllowed = $product->quantity + $originalQty;

        if (isset($this->sale_items[$itemKey])) {
            $currentCartQty = (int) $this->sale_items[$itemKey]['quantity'];
            if ($currentCartQty >= $maxAllowed) {
                session()->flash('failed', "Cannot add more. Max available stock is {$maxAllowed} (Stock: {$product->quantity} + In Sale: {$originalQty}) for [{$product->name}].");
                return;
            }
            $this->sale_items[$itemKey]['quantity'] += 1;
        } else {
            if ($maxAllowed < 1) {
                session()->flash('failed', "Product [{$product->name}] is out of stock!");
                return;
            }

            $this->products[] = $product;
            $this->sale_items[$itemKey] = [
                'key' => $itemKey,
                'sale_item_id' => null,
                'product_id' => $product->id,
                'item_name' => $product->name,
                'warranty_id' => $product->warranty?->id,
                'warranty_description' => $product->warranty?->description ?? '',
                'price' => (float) $product->price,
                'purchase_price' => (float) $product->purchase_price,
                'discount' => 0,
                'quantity' => 1,
                'attributes_and_values' => [],
                'total' => 0.00,
                'is_custom' => false,
            ];
        }

        $this->reset(['identifier']);
        $this->calculate();
    }

    public function openCustomModal()
    {
        $this->reset([
            'custom_item_name',
            'custom_price',
            'custom_purchase_price',
            'custom_quantity',
            'custom_warranty_id',
            'custom_warranty_description',
        ]);
        $this->custom_quantity = 1;
        $this->custom_purchase_price = 0;
        $this->showCustomModal = true;
    }

    public function closeCustomModal()
    {
        $this->showCustomModal = false;
    }

    public function setCustomSalePrice($amount)
    {
        $this->custom_price = $amount;
    }

    public function setCustomPurchasePrice($amount)
    {
        $this->custom_purchase_price = $amount;
    }

    public function addCustomItem()
    {
        $this->validate([
            'custom_item_name' => 'required|string|max:255',
            'custom_price' => 'required|numeric|min:0',
            'custom_purchase_price' => 'nullable|numeric|min:0',
            'custom_quantity' => 'required|integer|min:1',
            'custom_warranty_id' => 'nullable|exists:warranties,id',
            'custom_warranty_description' => 'nullable|string|max:1000',
        ]);

        $itemKey = 'custom_' . uniqid();

        $this->sale_items[$itemKey] = [
            'key' => $itemKey,
            'sale_item_id' => null,
            'product_id' => null,
            'item_name' => trim($this->custom_item_name),
            'warranty_id' => !empty($this->custom_warranty_id) ? $this->custom_warranty_id : null,
            'warranty_description' => trim($this->custom_warranty_description),
            'price' => (float) $this->custom_price,
            'purchase_price' => (float) ($this->custom_purchase_price ?: 0),
            'discount' => 0,
            'quantity' => (int) $this->custom_quantity,
            'attributes_and_values' => [],
            'total' => 0.00,
            'is_custom' => true,
        ];

        $this->closeCustomModal();
        $this->calculate();
    }

    public function delete($key)
    {
        unset($this->sale_items[$key]);
        $this->calculate();
    }

    public function setCustomer($customer_id)
    {
        $this->customer_id = $customer_id;
        $customer = Customer::find($customer_id);
        $this->customer_name = $customer?->name;
        $this->customer_phone_no = $customer?->phone_no;
        $this->customers = [];
    }

    protected function calculate()
    {
        $this->subtotal = 0;

        $this->sale_items = collect($this->sale_items)->map(function ($item) {
            try {
                $item['total'] = number_format($item['price'] * $item['quantity'], 2, '.', '');
                $item['total'] -= $item['discount'];
                $this->subtotal += $item['total'];
            } catch (\Throwable $th) {
                return $item;
            }
            return $item;
        })->toArray();

        try {
            $this->subtotal    = (float) $this->subtotal;
            $this->discount    = (float) collect($this->sale_items)->sum('discount');
            $this->gst_percent = (float) $this->gst_percent;

            $this->gst = $this->subtotal * ($this->gst_percent / 100);
            $this->grand_total = ($this->subtotal) + $this->gst;
        } catch (\Throwable $th) {
            return;
        }

        $this->subtotal    = number_format($this->subtotal, 2, '.', '');
        $this->gst         = number_format($this->gst, 2, '.', '');
        $this->grand_total = number_format($this->grand_total, 2, '.', '');
    }

    public function updateSale()
    {
        if (empty($this->customer_id)) {
            $this->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone_no' => 'required|string|max:255',
            ]);
        }

        if (empty($this->sale_items)) {
            session()->flash('failed', 'Cannot update sale with an empty cart. If you want to cancel the sale, use Delete Sale.');
            return;
        }

        $this->validate([
            'sale_items' => 'required|array',
            'sale_items.*' => 'required|array',
            'sale_items.*.product_id' => 'nullable|exists:products,id',
            'sale_items.*.item_name' => 'required|string|max:255',
            'sale_items.*.warranty_id' => 'nullable|exists:warranties,id',
            'sale_items.*.attributes_and_values' => 'nullable|array',
            'sale_items.*.quantity' => 'required|integer|min:1',
            'sale_items.*.discount' => 'required|numeric',
            'sale_items.*.price' => 'required|numeric',
            'sale_items.*.purchase_price' => 'nullable|numeric',
            'sale_items.*.total' => 'required|numeric',
        ]);

        // Pre-validate stock availability for all items
        $newQuantitiesByProduct = [];
        foreach ($this->sale_items as $item) {
            if (!empty($item['product_id'])) {
                $pid = $item['product_id'];
                $newQuantitiesByProduct[$pid] = ($newQuantitiesByProduct[$pid] ?? 0) + (int) $item['quantity'];
            }
        }

        foreach ($newQuantitiesByProduct as $pid => $newTotalQty) {
            $product = Product::find($pid);
            if (!$product) {
                session()->flash('failed', "Product ID {$pid} not found.");
                return;
            }

            $originalQty = $this->original_stock_quantities[$pid] ?? 0;
            $maxAllowed = $product->quantity + $originalQty;

            if ($newTotalQty > $maxAllowed) {
                session()->flash('failed', "Insufficient stock for [{$product->name}]. Requested: {$newTotalQty}, Max Available: {$maxAllowed} (Stock: {$product->quantity} + In Sale: {$originalQty}).");
                return;
            }
        }

        // Perform atomic update
        DB::transaction(function () use ($newQuantitiesByProduct) {
            // 1. Adjust inventory stock for differences
            $allProductIds = array_unique(array_merge(
                array_keys($this->original_stock_quantities),
                array_keys($newQuantitiesByProduct)
            ));

            foreach ($allProductIds as $pid) {
                $originalQty = $this->original_stock_quantities[$pid] ?? 0;
                $newQty = $newQuantitiesByProduct[$pid] ?? 0;
                $diff = $newQty - $originalQty;

                if ($diff !== 0) {
                    $product = Product::lockForUpdate()->find($pid);
                    if ($product) {
                        $product->decrement('quantity', $diff);
                    }
                }
            }

            // 2. Remove old sale items and recreate fresh items
            $this->sale->saleItems()->delete();

            $profit_total = 0;
            foreach ($this->sale_items as $item) {
                $purchase_price = 0;

                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    $purchase_price = $product ? (float) $product->purchase_price : (float) ($item['purchase_price'] ?? 0);
                } else {
                    $purchase_price = (float) ($item['purchase_price'] ?? 0);
                }

                $profit_per_unit = $item['price'] - $purchase_price;
                $profit_total_for_item = ($profit_per_unit * $item['quantity']) - $item['discount'];
                $profit_total += $profit_total_for_item;

                $this->sale->saleItems()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'] ?? null,
                    'warranty_id' => empty($item['warranty_id']) ? null : $item['warranty_id'],
                    'warranty_description' => !empty($item['warranty_description']) ? $item['warranty_description'] : null,
                    'attributes_and_values' => $item['attributes_and_values'] ?? [],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $purchase_price,
                    'price' => $item['price'],
                    'discount' => $item['discount'],
                    'total' => $item['total'],
                    'profit' => $profit_total_for_item,
                ]);
            }

            // 3. Update customer if newly entered
            $customerId = $this->customer_id;
            if (empty($customerId)) {
                $customer = Customer::create([
                    'name' => $this->customer_name,
                    'phone_no' => $this->customer_phone_no,
                ]);
                $customerId = $customer->id;
            }

            // 4. Update sale header
            $this->sale->update([
                'total_price' => $this->grand_total,
                'gst' => $this->gst,
                'discount' => $this->discount,
                'profit' => $profit_total,
                'customer_id' => $customerId,
            ]);
        });

        session()->flash('success', "Sale #{$this->sale->receipt_no} updated successfully with inventory synced!");
        return redirect()->route('sales.index');
    }

    public function render()
    {
        return view('livewire.sales.sale-edit', [
            'warranties' => Warranty::all(),
            'shop' => Shop::first(),
        ]);
    }
}
