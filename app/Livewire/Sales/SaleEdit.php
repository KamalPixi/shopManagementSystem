<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Warranty;
use App\Models\Shop;
use App\Models\Customer;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleEdit extends Component
{
    use WithFileUploads;

    public $sale_id;
    public $sale;

    public $identifier = '';
    public $products = [];
    public $sale_items = [];

    // Track original quantities for stock adjustment: [product_id => total_original_qty]
    public $original_stock_quantities = [];
    public $original_variation_quantities = [];

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

    // Payment fields
    public $payment_method = 'cash';
    public $payment_reference = '';
    public $payment_attachment;
    public $existing_attachment;

    // For adding custom item
    public $showCustomModal = false;
    public $custom_item_name = '';
    public $custom_price = '';
    public $custom_purchase_price = 0;
    public $custom_quantity = 1;
    public $custom_warranty_id = '';
    public $custom_warranty_description = '';
    public $custom_is_vatable = 1;
    public $custom_show_warranty_on_receipt = 1;

    public function mount($sale_id)
    {
        $this->sale_id = $sale_id;
        $this->sale = Sale::with(['saleItems.product', 'saleItems.productVariation', 'customer'])->findOrFail($sale_id);

        $shop = Shop::first();
        if ($shop) {
            $this->gst_percent = (float) $shop->gst_percent;
        }

        $this->customer_id = $this->sale->customer_id;
        $this->customer_name = $this->sale->customer?->name ?? 'Walk In';
        $this->customer_phone_no = $this->sale->customer?->phone_no ?? '';

        $this->payment_method = $this->sale->payment_method ?: 'cash';
        $this->payment_reference = $this->sale->payment_reference ?: '';
        $this->existing_attachment = $this->sale->payment_attachment;

        $this->original_stock_quantities = [];
        $this->original_variation_quantities = [];

        foreach ($this->sale->saleItems as $item) {
            $itemKey = !empty($item->product_id) ? 'prod_' . $item->product_id . ($item->product_variation_id ? '_' . $item->product_variation_id : '') : 'custom_' . $item->id;

            if ($item->product) {
                $this->products[] = $item->product;
                $this->original_stock_quantities[$item->product_id] = ($this->original_stock_quantities[$item->product_id] ?? 0) + (int) $item->quantity;
                if ($item->product_variation_id) {
                    $this->original_variation_quantities[$item->product_variation_id] = ($this->original_variation_quantities[$item->product_variation_id] ?? 0) + (int) $item->quantity;
                }
            }

            $this->sale_items[$itemKey] = [
                'key' => $itemKey,
                'sale_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_variation_id' => $item->product_variation_id,
                'variation_name' => $item->productVariation?->variation_name,
                'item_name' => $item->name,
                'serial_no' => $item->productVariation?->serial_no ?: $item->product?->serial_no,
                'sku' => $item->productVariation?->sku ?: $item->product?->sku,
                'warranty_id' => $item->warranty_id,
                'warranty_description' => $item->warranty_description ?? ($item->warranty?->description ?? ''),
                'price' => (float) $item->price,
                'purchase_price' => (float) $item->purchase_price,
                'discount' => (float) $item->discount,
                'quantity' => (int) $item->quantity,
                'attributes_and_values' => $item->attributes_and_values ?? [],
                'total' => (float) $item->total,
                'is_vatable' => (bool) $item->is_vatable,
                'show_warranty_on_receipt' => (bool) $item->show_warranty_on_receipt,
                'show_serial_on_receipt' => (bool) $item->show_serial_on_receipt,
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

        // When warranty_id changes for a cart line item, auto-fill warranty_description
        if (preg_match('/^sale_items\.(.+)\.warranty_id$/', $propertyName, $matches)) {
            $itemKey = $matches[1];
            if (isset($this->sale_items[$itemKey])) {
                $wid = $this->sale_items[$itemKey]['warranty_id'];
                if (!empty($wid)) {
                    $warranty = Warranty::find($wid);
                    $this->sale_items[$itemKey]['warranty_description'] = $warranty?->description ?? '';
                    $this->sale_items[$itemKey]['show_warranty_on_receipt'] = true;
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
                    $this->errorMessage = __('Quantity must be at least 1.');
                } elseif (!empty($this->sale_items[$itemKey]['product_variation_id'])) {
                    $variation = ProductVariation::find($this->sale_items[$itemKey]['product_variation_id']);
                    $originalQty = $this->original_variation_quantities[$variation?->id] ?? 0;
                    $availableTotal = ($variation ? $variation->quantity : 0) + $originalQty;
                    if ($qty > $availableTotal) {
                        $this->sale_items[$itemKey]['quantity'] = max(1, $availableTotal);
                        $this->errorMessage = __('Cannot exceed available variation stock (:avail in stock + :orig original)!', ['avail' => $variation ? $variation->quantity : 0, 'orig' => $originalQty]);
                    }
                } elseif (!empty($this->sale_items[$itemKey]['product_id'])) {
                    $pid = $this->sale_items[$itemKey]['product_id'];
                    $product = collect($this->products)->firstWhere('id', $pid) ?: Product::find($pid);
                    $originalQty = $this->original_stock_quantities[$pid] ?? 0;
                    $availableTotal = ($product ? $product->quantity : 0) + $originalQty;

                    if ($qty > $availableTotal) {
                        $this->sale_items[$itemKey]['quantity'] = max(1, $availableTotal);
                        $this->errorMessage = __('Cannot exceed available stock (:avail in stock + :orig original)!', [
                            'avail' => $product ? $product->quantity : 0,
                            'orig' => $originalQty,
                        ]);
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
            session()->flash('failed', __('No product found [:id]', ['id' => $this->identifier]));
            return;
        }

        $matchedVariation = $product->relationLoaded('matchedVariation') ? $product->matchedVariation : null;
        if ($product->has_variations && !$matchedVariation) {
            session()->flash('failed', __('Scan or enter a variation SKU or serial number to select its price and stock.'));
            return;
        }

        if ($matchedVariation) {
            $itemKey = 'var_' . $matchedVariation->id;
            if (isset($this->sale_items[$itemKey])) {
                $this->sale_items[$itemKey]['quantity'] += 1;
            } else {
                $this->products[] = $product;
                $this->sale_items[$itemKey] = [
                    'key' => $itemKey,
                    'sale_item_id' => null,
                    'product_id' => $product->id,
                    'product_variation_id' => $matchedVariation->id,
                    'variation_name' => $matchedVariation->variation_name,
                    'item_name' => $product->name . ' (' . $matchedVariation->variation_name . ')',
                    'serial_no' => $matchedVariation->serial_no ?: $product->serial_no,
                    'sku' => $matchedVariation->sku ?: $product->sku,
                    'warranty_id' => $product->warranty?->id,
                    'warranty_description' => $product->warranty?->description ?? '',
                    'price' => (float) $matchedVariation->price,
                    'purchase_price' => (float) $matchedVariation->purchase_price,
                    'discount' => 0,
                    'quantity' => 1,
                    'attributes_and_values' => $matchedVariation->attributes_and_values ?? ($product->attributes_and_values ?? []),
                    'total' => 0.00,
                    'is_vatable' => (bool) $product->is_vatable,
                    'show_warranty_on_receipt' => true,
                    'show_serial_on_receipt' => !empty($matchedVariation->serial_no ?: $product->serial_no),
                    'is_custom' => false,
                ];
            }
        } else {
            $itemKey = 'prod_' . $product->id;
            if (isset($this->sale_items[$itemKey])) {
                $this->sale_items[$itemKey]['quantity'] += 1;
            } else {
                $this->products[] = $product;
                $this->sale_items[$itemKey] = [
                    'key' => $itemKey,
                    'sale_item_id' => null,
                    'product_id' => $product->id,
                    'product_variation_id' => null,
                    'variation_name' => null,
                    'item_name' => $product->name,
                    'serial_no' => $product->serial_no,
                    'sku' => $product->sku,
                    'warranty_id' => $product->warranty?->id,
                    'warranty_description' => $product->warranty?->description ?? '',
                    'price' => (float) $product->price,
                    'purchase_price' => (float) $product->purchase_price,
                    'discount' => 0,
                    'quantity' => 1,
                    'attributes_and_values' => [],
                    'total' => 0.00,
                    'is_vatable' => (bool) $product->is_vatable,
                    'show_warranty_on_receipt' => true,
                    'show_serial_on_receipt' => !empty($product->serial_no),
                    'is_custom' => false,
                ];
            }
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
        $this->custom_is_vatable = 1;
        $this->custom_show_warranty_on_receipt = 1;
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
            'product_variation_id' => null,
            'variation_name' => null,
            'item_name' => trim($this->custom_item_name),
            'serial_no' => null,
            'sku' => null,
            'warranty_id' => !empty($this->custom_warranty_id) ? $this->custom_warranty_id : null,
            'warranty_description' => trim($this->custom_warranty_description),
            'price' => (float) $this->custom_price,
            'purchase_price' => (float) ($this->custom_purchase_price ?: 0),
            'discount' => 0,
            'quantity' => (int) $this->custom_quantity,
            'attributes_and_values' => [],
            'total' => 0.00,
            'is_vatable' => (bool) $this->custom_is_vatable,
            'show_warranty_on_receipt' => (bool) $this->custom_show_warranty_on_receipt,
            'show_serial_on_receipt' => false,
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
        $taxableSubtotal = 0;

        $this->sale_items = collect($this->sale_items)->map(function ($item) use (&$taxableSubtotal) {
            try {
                $item['total'] = number_format($item['price'] * $item['quantity'], 2, '.', '');
                $item['total'] -= (float) $item['discount'];
                $this->subtotal += (float) $item['total'];

                if (!empty($item['is_vatable'])) {
                    $taxableSubtotal += (float) $item['total'];
                }
            } catch (\Throwable $th) {
                return $item;
            }
            return $item;
        })->toArray();

        try {
            $this->subtotal = (float) $this->subtotal;
            $this->discount = (float) collect($this->sale_items)->sum('discount');
            $this->gst_percent = (float) $this->gst_percent;

            $this->gst = $taxableSubtotal * ($this->gst_percent / 100);
            $this->grand_total = $this->subtotal + $this->gst;
        } catch (\Throwable $th) {
            return;
        }

        $this->subtotal = number_format($this->subtotal, 2, '.', '');
        $this->gst = number_format($this->gst, 2, '.', '');
        $this->grand_total = number_format($this->grand_total, 2, '.', '');
    }

    public function updateSale()
    {
        if (empty($this->customer_id)) {
            $this->validate([
                'customer_name' => 'nullable|string|max:255',
                'customer_phone_no' => 'nullable|string|max:255',
            ]);
        }

        if (empty($this->sale_items)) {
            session()->flash('failed', __('No items in the sale!'));
            return;
        }

        $this->validate([
            'payment_method' => 'required|in:cash,card,bank_transfer',
            'payment_reference' => 'nullable|string|max:255',
            'payment_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'sale_items' => 'required|array',
            'sale_items.*.item_name' => 'required|string|max:255',
            'sale_items.*.quantity' => 'required|integer|min:1',
            'sale_items.*.price' => 'required|numeric|min:0',
            'sale_items.*.discount' => 'required|numeric|min:0',
            'sale_items.*.total' => 'required|numeric',
        ]);

        // Calculate new quantities separately for products and variations.
        $newQuantitiesByProduct = [];
        $newQuantitiesByVariation = [];
        foreach ($this->sale_items as $item) {
            if (!empty($item['product_variation_id'])) {
                $id = $item['product_variation_id'];
                $newQuantitiesByVariation[$id] = ($newQuantitiesByVariation[$id] ?? 0) + (int) $item['quantity'];
                if (!empty($item['product_id'])) {
                    $pid = $item['product_id'];
                    $newQuantitiesByProduct[$pid] = ($newQuantitiesByProduct[$pid] ?? 0) + (int) $item['quantity'];
                }
            } elseif (!empty($item['product_id'])) {
                $id = $item['product_id'];
                $newQuantitiesByProduct[$id] = ($newQuantitiesByProduct[$id] ?? 0) + (int) $item['quantity'];
            }
        }

        foreach ($newQuantitiesByVariation as $vid => $newQty) {
            $variation = ProductVariation::find($vid);
            $available = ($variation?->quantity ?? 0) + ($this->original_variation_quantities[$vid] ?? 0);
            if (!$variation || $newQty > $available) {
                session()->flash('failed', __('Insufficient stock for variation. Requested: :req, Available: :avail.', ['req' => $newQty, 'avail' => $available]));
                return;
            }
        }
        foreach ($newQuantitiesByProduct as $pid => $newQty) {
            $product = Product::find($pid);
            $available = ($product?->quantity ?? 0) + ($this->original_stock_quantities[$pid] ?? 0);
            if (!$product || $newQty > $available) {
                session()->flash('failed', __('Insufficient stock for product. Requested: :req, Available: :avail.', ['req' => $newQty, 'avail' => $available]));
                return;
            }
        }

        // Handle attachment upload
        $attachmentPath = $this->existing_attachment;
        if ($this->payment_attachment) {
            $directory = 'uploads/payments';
            if (!is_dir(public_path($directory))) {
                mkdir(public_path($directory), 0755, true);
            }
            $ext = $this->payment_attachment->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $ext;
            $attachmentPath = $this->payment_attachment->storeAs($directory, $filename, 'public_direct');
        }

        // Perform atomic update
        DB::transaction(function () use ($newQuantitiesByProduct, $newQuantitiesByVariation, $attachmentPath) {
            // Adjust variation stock first.
            $allVariationIds = array_unique(array_merge(array_keys($this->original_variation_quantities), array_keys($newQuantitiesByVariation)));
            foreach ($allVariationIds as $vid) {
                $diff = ($newQuantitiesByVariation[$vid] ?? 0) - ($this->original_variation_quantities[$vid] ?? 0);
                if ($diff !== 0) ProductVariation::lockForUpdate()->find($vid)?->decrement('quantity', $diff);
            }

            // Adjust parent stock for standalone product items.
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

            // Remove old sale items and recreate fresh items
            $this->sale->saleItems()->delete();

            $profit_total = 0;
            foreach ($this->sale_items as $item) {
                $purchase_price = 0;

                if (!empty($item['product_variation_id'])) {
                    $variation = ProductVariation::find($item['product_variation_id']);
                    $purchase_price = $variation ? (float) $variation->purchase_price : (float) ($item['purchase_price'] ?? 0);
                } elseif (!empty($item['product_id'])) {
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
                    'product_variation_id' => $item['product_variation_id'] ?? null,
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
                    'is_vatable' => !empty($item['is_vatable']),
                    'show_warranty_on_receipt' => !empty($item['show_warranty_on_receipt']),
                    'show_serial_on_receipt' => !empty($item['show_serial_on_receipt']),
                ]);
            }

            // Update customer
            $customerId = $this->customer_id;
            if (empty($customerId) && (!empty(trim($this->customer_name ?? '')) || !empty(trim($this->customer_phone_no ?? '')))) {
                $custName = trim($this->customer_name ?? '');
                $custPhone = trim($this->customer_phone_no ?? '');
                $customer = Customer::create([
                    'name' => $custName ?: ($custPhone ?: 'Customer'),
                    'phone_no' => $custPhone ?: null,
                ]);
                $customerId = $customer->id;
            } elseif (empty($customerId)) {
                $customerId = null;
            }

            // Update sale header
            $this->sale->update([
                'total_price' => $this->grand_total,
                'gst' => $this->gst,
                'discount' => $this->discount,
                'profit' => $profit_total,
                'customer_id' => $customerId,
                'payment_method' => $this->payment_method,
                'payment_reference' => !empty(trim($this->payment_reference)) ? trim($this->payment_reference) : null,
                'payment_attachment' => $attachmentPath,
                'updated_by' => auth()->id(),
            ]);
        });

        AuditLogger::log(
            'updated_sale',
            __(':user updated sale :receipt (Total: :total, Payment: :payment)', [
                'user' => auth()->user()->name,
                'receipt' => $this->sale->receipt_no,
                'total' => number_format($this->sale->total_price, 2),
                'payment' => ucfirst(str_replace('_', ' ', $this->payment_method))
            ]),
            $this->sale,
            ['receipt_no' => $this->sale->receipt_no, 'total_price' => $this->sale->total_price]
        );

        session()->flash('success', __('Sale :receipt updated successfully!', ['receipt' => $this->sale->receipt_no]));
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
