<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Warranty;
use App\Models\Shop;
use App\Models\Sale;
use App\Models\Customer;
use App\Services\SaleService;
use App\Services\PrintService;


class Pos extends Component
{
    public $identifier = '';

    public $products = [];
    public $sale_items = [];

    public $subtotal = 0;
    public $discount = 0;
    public $gst = 0;
    public $grand_total = 0;

    public $customer_name = '';
    public $customer_phone_no = '';

    // for suggestion & selection
    public $customers;
    public $customer_id;

    public $gst_percent = 0;

    public $custom_item_name = '';
    public $custom_price = '';
    public $custom_purchase_price = 0;
    public $custom_quantity = 1;
    public $custom_warranty_id = '';
    public $custom_warranty_description = '';
    public $showCustomModal = false;
    public $errorMessage = '';

    public function updated($propertyName) {
        $this->errorMessage = '';

        if ($propertyName == 'customer_name') {
            if (empty($this->customer_name)) {
                $this->customer_id = null;
                $this->customers = [];
                return;
            }
            $this->customers = Customer::where('name', 'like', '%'. $this->customer_name .'%')->get();
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

        // Validate stock if item quantity changed
        if (preg_match('/^sale_items\.(.+)\.quantity$/', $propertyName, $matches)) {
            $itemKey = $matches[1];
            if (isset($this->sale_items[$itemKey])) {
                $qty = (int) $this->sale_items[$itemKey]['quantity'];
                if ($qty < 1) {
                    $this->sale_items[$itemKey]['quantity'] = 1;
                    $this->errorMessage = 'Quantity must be at least 1.';
                } elseif (!empty($this->sale_items[$itemKey]['product_id'])) {
                    $product = collect($this->products)->firstWhere('id', $this->sale_items[$itemKey]['product_id']);
                    if (!$product) {
                        $product = Product::find($this->sale_items[$itemKey]['product_id']);
                    }
                    if ($product && $qty > $product->quantity) {
                        $this->sale_items[$itemKey]['quantity'] = max(1, $product->quantity);
                        $this->errorMessage = "Cannot exceed available stock! Only {$product->quantity} in stock for [{$product->name}].";
                    }
                }
            }
        }

        $this->calculate();
    }

    public function mount() {
        $shop = Shop::first();
        if ($shop) {
            $this->gst_percent = $shop->gst_percent;
        }
    }

    public function add() {
        $product = Product::findBySkuSerialOrModel($this->identifier);

        if (empty($product)) {
            session()->flash('failed', "No product found [{$this->identifier}]");
            return;
        }

        if ($product->quantity < 1) {
            session()->flash('failed', 'Out of stock!');
            return;
        }

        $itemKey = 'prod_' . $product->id;

        // increment count if already in cart
        if (isset($this->sale_items[$itemKey])) {
            $qtyLeft = $product->quantity - (int) $this->sale_items[$itemKey]['quantity'];
            if ($qtyLeft < 1) {
                return session()->flash('failed', 'Quantity out of stock!');
            }
            $this->sale_items[$itemKey]['quantity'] += 1;
        } else {
            $this->products[] = $product;
            $this->sale_items[$itemKey] = [
                'key' => $itemKey,
                'product_id' => $product->id,
                'item_name' => $product->name,
                'warranty_id' => $product->warranty?->id,
                'warranty_description' => $product->warranty?->description ?? '',
                'price' => $product->price,
                'purchase_price' => $product->purchase_price,
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

    public function openCustomModal() {
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

    public function closeCustomModal() {
        $this->showCustomModal = false;
    }

    public function setCustomSalePrice($amount) {
        $this->custom_price = $amount;
    }

    public function setCustomPurchasePrice($amount) {
        $this->custom_purchase_price = $amount;
    }

    public function addCustomItem() {
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

    public function sale() {

        if (empty($this->customer_id)) {
            $this->validate([
                'customer_name' => 'nullable|string|max:255',
                'customer_phone_no' => 'nullable|string|max:255',
            ]);
        }

        if (empty($this->sale_items)) {
            session()->flash('failed', 'No items in cart to sell!');
            return;
        }

        // validate sale items
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

        // Pre-validate that all catalog products have sufficient stock
        foreach ($this->sale_items as $item) {
            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    session()->flash('failed', "Product not found for item [{$item['item_name']}].");
                    return;
                }
                if ($product->quantity < $item['quantity']) {
                    session()->flash('failed', "Insufficient stock for [{$product->name}]. Requested: {$item['quantity']}, In stock: {$product->quantity}.");
                    return;
                }
            }
        }

        $sale = Sale::create([
            'total_price' => $this->grand_total,
            'gst' => $this->gst,
            'discount' => $this->discount,
            'created_by' => auth()->user()->id,
        ]);

        $profit_total = 0;
        foreach ($this->sale_items as $item) {
            $purchase_price = 0;

            if (!empty($item['product_id'])) {
                // Catalog product
                $product = Collect($this->products)->firstWhere('id', $item['product_id']);
                if (!$product) {
                    $product = Product::find($item['product_id']);
                }
                $purchase_price = $product ? $product->purchase_price : ($item['purchase_price'] ?? 0);

                // Decrease product quantity in stock
                if ($product) {
                    $product->update([
                        'quantity' => $product->quantity - $item['quantity']
                    ]);
                }
            } else {
                // Custom / On-the-fly item
                $purchase_price = (float) ($item['purchase_price'] ?? 0);
            }

            // Calculate the profit for the item
            $profit_per_unit = $item['price'] - $purchase_price;
            $profit_total_for_item = ($profit_per_unit * $item['quantity']) - $item['discount'];

            // Add this item's profit to total sale profit
            $profit_total += $profit_total_for_item;

            // Create the sale item entry
            $sale->saleItems()->create([
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

        // Update the sale with the total profit
        $sale->update(['profit' => $profit_total]);

        if (!empty($this->customer_id)) {
            $sale->update(['customer_id' => $this->customer_id]);
        } elseif (!empty(trim($this->customer_name ?? '')) || !empty(trim($this->customer_phone_no ?? ''))) {
            $custName = trim($this->customer_name ?? '');
            $custPhone = trim($this->customer_phone_no ?? '');
            $customer = Customer::create([
                'name' => $custName ?: ($custPhone ?: 'Customer'),
                'phone_no' => $custPhone ?: null,
            ]);
            $sale->update(['customer_id' => $customer->id]);
        } else {
            $sale->update(['customer_id' => null]);
        }

        $this->clearPos();

        // Send raw print command to physical/virtual POS printer
        $printService = new PrintService();
        $msg = $printService->printReceipt($sale);

        return session()->flash('message', $msg);
    }

    protected function calculate() {
        $this->subtotal = 0;

        $this->sale_items = collect($this->sale_items)->map(function ($item) {
            try {
                // Single unit total for the item
                $item['total'] = number_format($item['price'] * $item['quantity'], '2', '.', '');
                $item['total'] -= $item['discount'];
                $this->subtotal += $item['total'];
            } catch (\Throwable $th) {
                return $item;
            }
            return $item;
        })->toArray();

        try {
            // Calculate GST and Grand Total after mapping
            $this->subtotal     = (float) $this->subtotal;
            $this->discount     = (float) collect($this->sale_items)->sum('discount');
            $this->gst_percent  = (float) $this->gst_percent;

            $this->gst = $this->subtotal * ($this->gst_percent / 100);
            $this->grand_total = ($this->subtotal) + $this->gst;

        } catch (\Throwable $th) {
            return;
        }

        // Format numbers (2 decimal places)
        $this->subtotal = number_format($this->subtotal, 2, '.', '');
        $this->gst = number_format($this->gst, 2, '.', '');
        $this->grand_total = number_format($this->grand_total, 2, '.', '');
    }

    public function printSaleReceipt($sale_id) {
        $s = new SaleService();
        $url = $s->generateSaleReceipt($sale_id);
        
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function delete($key) {
        $item = $this->sale_items[$key] ?? null;
        if ($item && !empty($item['product_id'])) {
            $productId = $item['product_id'];
            $this->products = Collect($this->products)->filter(fn($p) => $p->id != $productId);
        }
        unset($this->sale_items[$key]);
        $this->calculate();
    }

    public function setCustomer($customer_id) {
        $this->customer_id = $customer_id;
        $customer = Customer::find($customer_id);
        $this->customer_name = $customer?->name;
        $this->customer_phone_no = $customer?->phone_no;
        $this->customers = [];
    }

    public function clearPos() {
        $this->reset([
            'identifier',
            'products',
            'sale_items',
            'subtotal',
            'discount',
            'gst',
            'grand_total',
            'customer_name',
            'customer_phone_no',
            'custom_item_name',
            'custom_price',
            'custom_purchase_price',
            'custom_quantity',
            'custom_warranty_id',
            'showCustomModal',
        ]);
    }

    public function render()
    {
        return view('livewire.pos', [
            'warranties' => Warranty::all(),
            'shop' => Shop::first(),
        ]);
    }
}
