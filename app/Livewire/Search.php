<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class Search extends Component
{

    public $search;
    public $products = [];

    public function updated($propertyName) {
        if ($propertyName == 'search') {
            if(empty($this->search)) {
                $this->products = [];
                return;
            }

            $this->products = Product::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('sku', 'like', '%' . $this->search . '%')
            ->orWhere('model_no', 'like', '%' . $this->search . '%')
            ->orWhere('serial_no', 'like', '%' . $this->search . '%')
            ->limit(10)->get();
        }
    }

    public function searchProduct($id) {
        return redirect()->route('stocks.show', ['product_id' => $id]);
    }

    public function render()
    {
        return view('livewire.search');
    }
}
