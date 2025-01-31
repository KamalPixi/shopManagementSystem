<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Warranty;
use App\Models\Shop;
use App\Models\Sale;
use App\Models\Customer;

class Pos extends Component
{
    public $identifier = '';

    public $products = [];
    public $sale_items = [];

    public $subtotal = 0;
    public $discount = 0;
    public $gst = 0;
    public $grand_total = 0;

    public $customer_name;
    public $customer_phone_no;

    // for suggestion & selection
    public $customers;
    public $customer_id;

    public $gst_percent = 0;

    public function updated($propertyName) {
        if ($propertyName == 'customer_name') {
            if (empty($this->customer_name)) {
                $this->customer_id = null;
                $this->customers = [];
                return;
            }
            $this->customers = Customer::where('name', 'like', '%'. $this->customer_name .'%')->get();
            return;
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

        $adyExists = Collect($this->products)->contains(function ($item) use ($product) {
            return $item->id == $product->id;
        });

        if ($adyExists) {
            session()->flash('failed', "Already exists.");
            return;
        }

        $this->products[] = $product;
        $this->sale_items[$product->id] = [
            'product_id' => $product->id,
            'warranty_id' => $product->warranty?->id,
            'price' => $product->price,
            'quantity' => 1,
            'attributes_and_values' => [],
            'total' => 0.00,
        ];

        $this->reset(['identifier']);
        $this->calculate();
    }

    public function sale() {

        if (empty($this->customer_id)) {
            $this->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone_no' => 'required|string|max:255',
            ]);
        }


        $sale = Sale::create([
            'total_price' => $this->grand_total,
            'gst' => $this->gst,
            'discount' => $this->discount,
        ]);

        // validate sale items
        $this->validate([
            'sale_items' => 'required|array',
            'sale_items.*' => 'required|array',
            'sale_items.*.product_id' => 'required|exists:products,id',
            'sale_items.*.warranty_id' => 'nullable|exists:warranties,id',
            'sale_items.*.attributes_and_values' => 'nullable|array',
            'sale_items.*.quantity' => 'required|integer',
            'sale_items.*.price' => 'required|numeric',
            'sale_items.*.total' => 'required|numeric',
        ]);

        $profit_total = 0;
        foreach ($this->sale_items as $item) {
            // Calculate the profit for the single item
            $product = Collect($this->products)->firstWhere('id', $item['product_id']);
            $profit = $item['price'] - $product->purchase_price; // Assuming $product->cost holds the cost price of the product
        
            // Add this item profit to the total sale profit
            $profit_total += $profit;
        
            // Create the sale item entry
            $sale->saleItems()->create([
                'product_id' => $item['product_id'],
                'warranty_id' => empty($item['warranty_id']) ? null : $item['warranty_id'],
                'attributes_and_values' => $item['attributes_and_values'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['total'],
                'profit' => $profit,
            ]);
        
            // Decrease product quantity in stock
            $product->update([
                'quantity' => $product->quantity - $item['quantity']
            ]);
        }
        
        // Update the sale with the total profit
        $sale->update(['profit' => $profit_total]);
        
        
        if(!empty($this->customer_id)) {
            $sale->update(['customer_id' => $this->customer_id]);
        }else {
            $customer = Customer::create([
                'name' => $this->customer_name,
                'phone_no' => $this->customer_phone_no,
            ]);
            $sale->update(['customer_id' => $customer->id]);
        }

        $this->clearPos();
        session()->flash('success', 'Product sale success.');
    }

    protected function calculate() {
        $this->subtotal = 0;

        $this->sale_items = collect($this->sale_items)->map(function ($item) {
            try {
                // Single unit total for the item
                $item['total'] = number_format($item['price'] * $item['quantity'], '2', '.', '');
                $this->subtotal += $item['total'];
            } catch (\Throwable $th) {
                return $item;
            }
            return $item;
        })->toArray();

        try {
            // Calculate GST and Grand Total after mapping
            $this->gst = $this->subtotal * ($this->gst_percent / 100);
            $this->grand_total = ($this->subtotal - $this->discount) + $this->gst;
        } catch (\Throwable $th) {
            return;
        }

        // Format numbers (2 decimal places)
        $this->subtotal = number_format($this->subtotal, 2, '.', '');
        $this->gst = number_format($this->gst, 2, '.', '');
        $this->grand_total = number_format($this->grand_total, 2, '.', '');
    }


    public function printSaleReceipt($sale_id) {
        
    }


    public function delete($id) {
        $this->sale_items = Collect($this->sale_items)->filter(fn($item) => $item['product_id'] != $id)->toArray();
        $this->products = Collect($this->products)->filter(fn($item) => $item->id != $id);
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
