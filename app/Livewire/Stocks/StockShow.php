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
        return view('livewire.stocks.stock-show', [
            'product' => Product::find($this->product_id)
        ]);
    }
}
