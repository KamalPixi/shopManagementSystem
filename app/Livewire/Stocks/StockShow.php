<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use App\Models\Product;

class StockShow extends Component
{
    public $product_id;

    public function mount($product_id) {
        $this->product_id = $product_id;
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('stock:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.stocks.stock-show', [
            'product' => Product::find($this->product_id)
        ]);
    }
}
