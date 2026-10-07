<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class Search extends Component
{
    public $search = '';
    public $products = [];

    public function updated($propertyName)
    {
        if ($propertyName === 'search') {
            $this->performSearch();
        }
    }

    public function updatedSearch()
    {
        $this->performSearch();
    }

    private function performSearch()
    {
        $term = trim($this->search);
        if (empty($term)) {
            $this->products = [];
            return;
        }

        $likeTerm = '%' . $term . '%';
        $this->products = Product::where('name', 'like', $likeTerm)
            ->orWhere('sku', 'like', $likeTerm)
            ->orWhere('model_no', 'like', $likeTerm)
            ->orWhere('serial_no', 'like', $likeTerm)
            ->orWhereHas('variations', function ($q) use ($likeTerm) {
                $q->where('sku', 'like', $likeTerm)
                  ->orWhere('serial_no', 'like', $likeTerm)
                  ->orWhere('variation_name', 'like', $likeTerm);
            })
            ->with(['variations'])
            ->limit(10)
            ->get();
    }

    public function searchProduct($id = null)
    {
        if (!empty($id)) {
            $this->products = [];
            return redirect()->route('stocks.show', ['product_id' => $id]);
        }

        $term = trim($this->search);
        if (empty($term)) {
            return;
        }

        // Direct match by SKU / Serial / Model
        $matched = Product::findBySkuSerialOrModel($term);
        if ($matched) {
            $this->products = [];
            return redirect()->route('stocks.show', ['product_id' => $matched->id]);
        }

        // If products exist in dropdown, go to the first one
        if (count($this->products) > 0) {
            $first = is_array($this->products) ? $this->products[0] : $this->products->first();
            $firstId = is_array($first) ? $first['id'] : $first->id;
            $this->products = [];
            return redirect()->route('stocks.show', ['product_id' => $firstId]);
        }

        // Otherwise go to stocks index
        $this->products = [];
        return redirect()->route('stocks.index');
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->products = [];
    }

    public function render()
    {
        return view('livewire.search');
    }
}

