<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use App\Models\Product;
use App\Models\Sale;

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

        $product = Product::with(['variations', 'category', 'brand', 'warranty', 'productPurchases.supplier', 'productPurchases.createdBy', 'saleItems'])->findOrFail($this->product_id);
        $sale_ids = $product->saleItems->pluck('sale_id')->unique();

        return view('livewire.stocks.stock-show', [
            'product' => $product,
            'sales' => Sale::with(['saleItems.product', 'saleItems.productVariation', 'customer', 'createdBy'])->whereIn('id', $sale_ids)->get(),
        ]);
    }
}
