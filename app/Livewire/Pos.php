<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Warranty;

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

    public function updated($propertyName) {
        $this->calculate();
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
            'total' => 0.00,
        ];

        $this->reset(['identifier']);
        $this->calculate();
    }

    public function sale() {
        dd($this->sale_items);
    }

    protected function calculate() {
        $this->sale_items = Collect($this->sale_items)->map(function($item) {
            try {
                $item['total'] = number_format($item['price'] * $item['quantity'], 2, '.', '');
                // total calculation
                $this->subtotal += $item['total'];
                $this->grand_total = ($this->subtotal - $this->discount) + $this->gst;
                $this->subtotal = number_format($this->subtotal, 2, '.', '');
                $this->grand_total = number_format($this->grand_total, 2, '.', '');
            } catch (\Throwable $th) {
                return $item;
            }
            return $item;
        })->toArray();
    }

    public function delete($id) {
        $this->sale_items = Collect($this->sale_items)->filter(fn($item) => $item['product_id'] != $id)->toArray();
        $this->products = Collect($this->products)->filter(fn($item) => $item->id != $id)->toArray();
    }

    public function render()
    {
        return view('livewire.pos', [
            'warranties' => Warranty::all()
        ]);
    }
}
