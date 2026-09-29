<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Warranty;
use App\Models\Shop;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\CashRegisterSession;
use App\Services\SaleService;
use App\Services\PrintService;
use App\Services\AuditLogger;
use Illuminate\Support\Str;

class Pos extends Component
{
    use WithFileUploads;

    public $identifier = '';
    public $pendingVariationProductId = null;
    public $selectedVariationId = null;

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

    // Payment methods & reference
    public $payment_method = 'cash'; // cash, card, bank_transfer, other
    public $payment_reference = '';
    public $payment_attachment;

    // Custom Item Modal
    public $custom_item_name = '';
    public $custom_price = '';
    public $custom_purchase_price = 0;
    public $custom_quantity = 1;
    public $custom_warranty_id = '';
    public $custom_warranty_description = '';
    public $custom_is_vatable = 1;
    public $custom_show_warranty_on_receipt = 1;
    public $showCustomModal = false;
    public $errorMessage = '';

    // Register Opening Modal for POS
    public $showOpenRegisterModal = false;
    public $pos_opening_balance = 0;
    public $pos_opening_note = '';

    // Register Close Previous Day Modal for POS (Option B)
    public $showClosePreviousDayModal = false;
    public $overdueSession = null;
    public $overdueExpected = 0;
    public $pos_closing_balance = 0;
    public $pos_closing_note = '';
    public $overdueDifference = 0;

    public function updated($propertyName) {
        $this->errorMessage = '';

        if ($propertyName === 'pos_closing_balance') {
            $this->overdueDifference = round((float) $this->pos_closing_balance - (float) $this->overdueExpected, 2);
            return;
        }

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
                    if ($variation && $qty > $variation->quantity) {
                        $this->sale_items[$itemKey]['quantity'] = max(1, $variation->quantity);
                        $this->errorMessage = __('Cannot exceed available variation stock! Only :qty in stock.', ['qty' => $variation->quantity]);
                    }
                } elseif (!empty($this->sale_items[$itemKey]['product_id'])) {
                    $product = collect($this->products)->firstWhere('id', $this->sale_items[$itemKey]['product_id']);
                    if (!$product) {
                        $product = Product::find($this->sale_items[$itemKey]['product_id']);
                    }
                    if ($product && $qty > $product->quantity) {
                        $this->sale_items[$itemKey]['quantity'] = max(1, $product->quantity);
                        $this->errorMessage = __('Cannot exceed available stock! Only :qty in stock for [:name].', ['qty' => $product->quantity, 'name' => $product->name]);
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
        $selectedVariation = $this->selectedVariationId ? ProductVariation::with('product')->find($this->selectedVariationId) : null;
        $product = $selectedVariation?->product ?: Product::findBySkuSerialOrModel($this->identifier);
        $this->selectedVariationId = null;

        if (empty($product)) {
            session()->flash('failed', __('No product found [:id]', ['id' => $this->identifier]));
            return;
        }

        // Check if a specific variation was matched
        $matchedVariation = $selectedVariation ?: ($product->relationLoaded('matchedVariation') ? $product->matchedVariation : null);
        if ($product->has_variations && !$matchedVariation) {
            $this->pendingVariationProductId = $product->id;
            $this->identifier = '';
            return;
        }

        $this->pendingVariationProductId = null;

        if ($matchedVariation) {
            if ($matchedVariation->quantity < 1) {
                session()->flash('failed', __('Variation [:name] is out of stock!', ['name' => $matchedVariation->variation_name]));
                return;
            }

            $itemKey = 'var_' . $matchedVariation->id;

            if (isset($this->sale_items[$itemKey])) {
                $qtyLeft = $matchedVariation->quantity - (int) $this->sale_items[$itemKey]['quantity'];
                if ($qtyLeft < 1) {
                    return session()->flash('failed', __('Variation quantity out of stock!'));
                }
                $this->sale_items[$itemKey]['quantity'] += 1;
            } else {
                $this->products[] = $product;
                $this->sale_items[$itemKey] = [
                    'key' => $itemKey,
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
                    'stock' => $matchedVariation->quantity,
                ];
            }
        } else {
            // Standalone product or master product
            if ($product->quantity < 1) {
                session()->flash('failed', __('Out of stock!'));
                return;
            }

            $itemKey = 'prod_' . $product->id;

            // increment count if already in cart
            if (isset($this->sale_items[$itemKey])) {
                $qtyLeft = $product->quantity - (int) $this->sale_items[$itemKey]['quantity'];
                if ($qtyLeft < 1) {
                    return session()->flash('failed', __('Quantity out of stock!'));
                }
                $this->sale_items[$itemKey]['quantity'] += 1;
            } else {
                $this->products[] = $product;
                $this->sale_items[$itemKey] = [
                    'key' => $itemKey,
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
                    'stock' => $product->quantity,
                ];
            }
        }

        $this->reset(['identifier']);
        $this->calculate();
    }

    public function selectVariation($variationId)
    {
        $variation = ProductVariation::where('product_id', $this->pendingVariationProductId)->find($variationId);
        if (!$variation) {
            session()->flash('failed', __('Variation not found. Search the product again.'));
            return;
        }
        $this->selectedVariationId = $variation->id;
        $this->add();
    }

    public function cancelVariationSelection()
    {
        $this->pendingVariationProductId = null;
    }

    public function openCustomModal() {
        $this->reset([
            'custom_item_name',
            'custom_price',
            'custom_purchase_price',
            'custom_quantity',
            'custom_warranty_id',
            'custom_warranty_description',
            'custom_is_vatable',
            'custom_show_warranty_on_receipt',
        ]);
        $this->custom_quantity = 1;
        $this->custom_purchase_price = 0;
        $this->custom_is_vatable = 1;
        $this->custom_show_warranty_on_receipt = 1;
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

    public function openRegisterModal()
    {
        $this->pos_opening_balance = 0;
        $this->pos_opening_note = '';
        $this->showOpenRegisterModal = true;
    }

    public function closeRegisterModal()
    {
        $this->showOpenRegisterModal = false;
    }

    public function openClosePreviousDayModal()
    {
        $overdueSession = CashRegisterSession::previousDayUnclosedSession();
        if ($overdueSession) {
            $this->overdueSession = $overdueSession;
            $this->overdueExpected = $overdueSession->calculateExpectedCash();
            $this->pos_closing_balance = $overdueSession->calculateExpectedCash();
            $this->overdueDifference = 0;
            $this->pos_closing_note = '';
            $this->showClosePreviousDayModal = true;
        }
    }

    public function closeClosePreviousDayModal()
    {
        $this->showClosePreviousDayModal = false;
        $this->overdueSession = null;
        $this->overdueExpected = 0;
        $this->overdueDifference = 0;
    }

    public function submitClosePreviousDay()
    {
        $this->validate([
            'pos_closing_balance' => 'required|numeric|min:0',
            'pos_closing_note' => 'nullable|string|max:1000',
        ]);

        $overdueSession = CashRegisterSession::previousDayUnclosedSession();
        if (!$overdueSession) {
            $this->showClosePreviousDayModal = false;
            return;
        }

        $expected = $overdueSession->calculateExpectedCash();
        $actual = (float) $this->pos_closing_balance;
        $diff = round($actual - $expected, 2);

        $overdueSession->update([
            'closing_balance' => $actual,
            'expected_closing_balance' => $expected,
            'difference' => $diff,
            'closed_by' => auth()->id(),
            'closed_at' => now(),
            'note' => $this->pos_closing_note,
            'status' => 'closed',
        ]);

        AuditLogger::log(
            'register_closed_delayed',
            __(':user closed previous day\'s register (opened :date). Expected: :exp, Actual: :act, Diff: :diff', [
                'user' => auth()->user()->name,
                'date' => $overdueSession->opened_at->format('d/m/Y'),
                'exp' => number_format($expected, 2),
                'act' => number_format($actual, 2),
                'diff' => number_format($diff, 2)
            ]),
            $overdueSession,
            [
                'expected' => $expected,
                'closing_balance' => $actual,
                'difference' => $diff,
                'note' => $this->pos_closing_note,
                'is_delayed_close' => true,
                'opened_at' => $overdueSession->opened_at->toDateTimeString(),
            ]
        );

        $this->showClosePreviousDayModal = false;
        // Prompt for today's opening balance immediately
        $this->openRegisterModal();
        session()->flash('success', __('Previous day\'s drawer closed successfully. Please now enter today\'s opening balance.'));
    }

    public function submitOpeningBalance()
    {
        // First verify no past day session remains unclosed
        $overdueSession = CashRegisterSession::previousDayUnclosedSession();
        if ($overdueSession) {
            $this->showOpenRegisterModal = false;
            $this->openClosePreviousDayModal();
            session()->flash('failed', __('Unclosed register from :date detected! Please close yesterday\'s drawer first.', ['date' => $overdueSession->opened_at->format('d/m/Y')]));
            return;
        }

        $this->validate([
            'pos_opening_balance' => 'required|numeric|min:0',
            'pos_opening_note' => 'nullable|string|max:1000',
        ]);

        $activeSession = CashRegisterSession::currentOpenSession();
        if ($activeSession && $activeSession->opened_at->isToday()) {
            $this->showOpenRegisterModal = false;
            session()->flash('success', __('Cash register is already open for today.'));
            return;
        }

        $session = CashRegisterSession::create([
            'user_id' => auth()->id(),
            'opening_balance' => $this->pos_opening_balance,
            'opened_at' => now(),
            'status' => 'open',
            'note' => $this->pos_opening_note,
        ]);

        AuditLogger::log(
            'register_opened',
            __(':user opened daily cash register with opening balance of :amount', [
                'user' => auth()->user()->name,
                'amount' => number_format($this->pos_opening_balance, 2)
            ]),
            $session,
            ['opening_balance' => $this->pos_opening_balance]
        );

        $this->showOpenRegisterModal = false;
        session()->flash('success', __('Cash register session opened successfully. You can now complete sales.'));
    }

    public function sale() {
        // Option B Strict Date Cutoff:
        // 1. Check if an unclosed session from yesterday/past days exists -> MUST close it first!
        $overdueSession = CashRegisterSession::previousDayUnclosedSession();
        if ($overdueSession) {
            $this->openClosePreviousDayModal();
            $msg = __('Unclosed register from :date detected! You must count and close yesterday\'s drawer before opening today\'s register or completing sales.', ['date' => $overdueSession->opened_at->format('d M Y')]);
            $this->errorMessage = $msg;
            session()->flash('failed', $msg);
            return;
        }

        // 2. Check if today's register is opened -> MUST enter today's opening balance!
        $todaySession = CashRegisterSession::todayOpenSession();
        if (!$todaySession) {
            $this->openRegisterModal();
            $msg = __('Cannot proceed with selling! Today\'s opening balance has not been entered yet. Please enter today\'s opening balance first.');
            $this->errorMessage = $msg;
            session()->flash('failed', $msg);
            return;
        }

        if (empty($this->customer_id)) {
            $this->validate([
                'customer_name' => 'nullable|string|max:255',
                'customer_phone_no' => 'nullable|string|max:255',
            ]);
        }

        if (empty($this->sale_items)) {
            session()->flash('failed', __('No items in cart to sell!'));
            return;
        }

        // Validate sale items
        $this->validate([
            'payment_method' => 'required|in:cash,card,bank_transfer,other',
            'payment_reference' => $this->payment_method === 'other' ? 'required|string|max:255' : 'nullable|string|max:255',
            'payment_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'sale_items' => 'required|array',
            'sale_items.*' => 'required|array',
            'sale_items.*.product_id' => 'nullable|exists:products,id',
            'sale_items.*.product_variation_id' => 'nullable|exists:product_variations,id',
            'sale_items.*.item_name' => 'required|string|max:255',
            'sale_items.*.warranty_id' => 'nullable|exists:warranties,id',
            'sale_items.*.attributes_and_values' => 'nullable|array',
            'sale_items.*.quantity' => 'required|integer|min:1',
            'sale_items.*.discount' => 'required|numeric',
            'sale_items.*.price' => 'required|numeric',
            'sale_items.*.purchase_price' => 'nullable|numeric',
            'sale_items.*.total' => 'required|numeric',
        ]);

        // Pre-validate stock for catalog products & variations
        foreach ($this->sale_items as $itemKey => $item) {
            if (!empty($item['product_variation_id'])) {
                $variation = ProductVariation::find($item['product_variation_id']);
                if (!$variation || $variation->product_id != ($item['product_id'] ?? null) || $variation->quantity < $item['quantity']) {
                    session()->flash('failed', __('Insufficient stock for variation [:name]. Requested: :req, Available: :stock.', [
                        'name' => $item['item_name'],
                        'req' => $item['quantity'],
                        'stock' => $variation ? $variation->quantity : 0,
                    ]));
                    return;
                }
                // Always persist the selected variation's canonical attributes in the cart.
                if (isset($this->sale_items[$itemKey])) {
                    $this->sale_items[$itemKey]['attributes_and_values'] = $variation->attributes_and_values ?? [];
                    $this->sale_items[$itemKey]['variation_name'] = $variation->variation_name;
                    $this->sale_items[$itemKey]['sku'] = $variation->sku ?: $this->sale_items[$itemKey]['sku'];
                    $this->sale_items[$itemKey]['serial_no'] = $variation->serial_no ?: $this->sale_items[$itemKey]['serial_no'];
                }
            } elseif (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if (!$product) {
                    session()->flash('failed', __('Product not found for item [:name].', ['name' => $item['item_name']]));
                    return;
                }
                if ($product->quantity < $item['quantity']) {
                    session()->flash('failed', __('Insufficient stock for [:name]. Requested: :req, In stock: :stock.', [
                        'name' => $product->name,
                        'req' => $item['quantity'],
                        'stock' => $product->quantity,
                    ]));
                    return;
                }
            }
        }

        // Handle payment attachment upload
        $attachmentPath = null;
        if ($this->payment_attachment) {
            $directory = 'uploads/payments';
            if (!is_dir(public_path($directory))) {
                mkdir(public_path($directory), 0755, true);
            }
            $ext = $this->payment_attachment->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $ext;
            $attachmentPath = $this->payment_attachment->storeAs($directory, $filename, 'public_direct');
        }

        $currentSession = CashRegisterSession::currentOpenSession();

        $sale = Sale::create([
            'total_price' => $this->grand_total,
            'gst' => $this->gst,
            'discount' => $this->discount,
            'created_by' => auth()->user()->id,
            'payment_method' => $this->payment_method,
            'payment_reference' => !empty(trim($this->payment_reference)) ? trim($this->payment_reference) : null,
            'payment_attachment' => $attachmentPath,
            'cash_register_session_id' => $currentSession?->id,
        ]);

        $profit_total = 0;
        foreach ($this->sale_items as $item) {
            $purchase_price = 0;

            if (!empty($item['product_variation_id'])) {
                $variation = ProductVariation::find($item['product_variation_id']);
                $purchase_price = $variation ? (float) $variation->purchase_price : ($item['purchase_price'] ?? 0);

                if ($variation) {
                    $variation->decrement('quantity', (int) $item['quantity']);
                }

                // Also decrease total product stock
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $product->decrement('quantity', (int) $item['quantity']);
                    }
                }
            } elseif (!empty($item['product_id'])) {
                $product = collect($this->products)->firstWhere('id', $item['product_id']) ?: Product::find($item['product_id']);
                $purchase_price = $product ? (float) $product->purchase_price : ($item['purchase_price'] ?? 0);

                if ($product) {
                    $product->decrement('quantity', (int) $item['quantity']);
                }
            } else {
                $purchase_price = (float) ($item['purchase_price'] ?? 0);
            }

            // Calculate profit for item
            $profit_per_unit = $item['price'] - $purchase_price;
            $profit_total_for_item = ($profit_per_unit * $item['quantity']) - $item['discount'];
            $profit_total += $profit_total_for_item;

            // Create the sale item entry
            $sale->saleItems()->create([
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

        // Update the sale with total profit
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

        // Record Audit Log for who performed the sale
        AuditLogger::log(
            'pos_sale',
            __(':user completed sale :receipt for :total via :payment (Items: :items)', [
                'user' => auth()->user()->name,
                'receipt' => $sale->receipt_no,
                'total' => number_format($sale->total_price, 2),
                'payment' => ucfirst(str_replace('_', ' ', $sale->payment_method)),
                'items' => count($this->sale_items)
            ]),
            $sale,
            [
                'receipt_no' => $sale->receipt_no,
                'total_price' => $sale->total_price,
                'payment_method' => $sale->payment_method,
                'payment_reference' => $sale->payment_reference,
                'items_count' => count($this->sale_items)
            ]
        );

        $this->clearPos();

        // Print physical/virtual receipt
        $printService = new PrintService();
        $msg = $printService->printReceipt($sale);

        return session()->flash('message', $msg);
    }

    protected function calculate() {
        $this->subtotal = 0;
        $taxableSubtotal = 0;

        $this->sale_items = collect($this->sale_items)->map(function ($item) use (&$taxableSubtotal) {
            try {
                $item['total'] = number_format($item['price'] * $item['quantity'], '2', '.', '');
                $item['total'] -= $item['discount'];
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

            // Only calculate GST on items flagged as VATable!
            $this->gst = $taxableSubtotal * ($this->gst_percent / 100);
            $this->grand_total = ($this->subtotal) + $this->gst;
        } catch (\Throwable $th) {
            return;
        }

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
        unset($this->sale_items[$key]);
        $this->calculate();
    }

    public function incrementQuantity($key)
    {
        if (isset($this->sale_items[$key])) {
            $item = $this->sale_items[$key];
            // Check stock if linked to product/variant
            if (!empty($item['product_variation_id'])) {
                $variation = ProductVariation::find($item['product_variation_id']);
                if ($variation && $item['quantity'] >= $variation->quantity) {
                    session()->flash('failed', __('Maximum stock reached for :name (:stock left)', ['name' => $item['item_name'], 'stock' => $variation->quantity]));
                    return;
                }
            } elseif (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                if ($product && $item['quantity'] >= $product->quantity) {
                    session()->flash('failed', __('Maximum stock reached for :name (:stock left)', ['name' => $item['item_name'], 'stock' => $product->quantity]));
                    return;
                }
            }

            $this->sale_items[$key]['quantity'] += 1;
            $this->calculate();
        }
    }

    public function decrementQuantity($key)
    {
        if (isset($this->sale_items[$key])) {
            if ($this->sale_items[$key]['quantity'] > 1) {
                $this->sale_items[$key]['quantity'] -= 1;
                $this->calculate();
            } else {
                $this->delete($key);
            }
        }
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
            'payment_method',
            'payment_reference',
            'payment_attachment',
            'showCustomModal',
        ]);
        $this->payment_method = 'cash';
    }

    public function render()
    {
        $currentSession = CashRegisterSession::currentOpenSession();
        $todaySession = CashRegisterSession::todayOpenSession();
        $overdueSession = CashRegisterSession::previousDayUnclosedSession();

        return view('livewire.pos', [
            'warranties' => Warranty::all(),
            'shop' => Shop::first(),
            'currentSession' => $currentSession,
            'todaySession' => $todaySession,
            'overdueSession' => $overdueSession,
        ]);
    }
}
