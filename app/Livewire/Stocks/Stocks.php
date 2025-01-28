<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Product;
use App\Services\BarcodeService;
use App\Services\StockService;
use App\Models\Shop;

class Stocks extends Component
{
    use WithPagination;

    public $name;
    public $sku;
    public $model_no;
    public $category_id = '';

    public function printStocks() {
        $query = $this->buildQuery();

        $service = new StockService();

        $data = [
            'shop' => Shop::first(),
            'products' => $query->get(),
            'stock_date' => '-',
            'stock_print_date' => date('Y-m-d'),
        ];
        
        $url = $service->printStocks($data);
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printBarcode($id) {
        $product = Product::findOrFail($id);

        // create barcode pdf
        $barcodeService = new BarcodeService();
        $url = $barcodeService->generateBarcode([
            'sku_array' => [$product->sku]
        ]);

        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printBarcodes() {
        $query = $this->buildQuery();

        $barcodeService = new BarcodeService();
        $url = $barcodeService->generateBarcode([
            'sku_array' => $query->get()->pluck('sku')->toArray()
        ]);

        $this->dispatch('print-pdf', ['url' => $url]);
    }

    private function buildQuery()
    {
        $query = Product::query();

        if (!empty($this->name)) {
            $query->where('name', 'like', '%' . $this->name . '%');
        }
        if (!empty($this->sku)) {
            $query->where('sku', 'like', '%' . $this->sku . '%');
        }
        if (!empty($this->model_no)) {
            $query->where('model_no', 'like', '%' . $this->model_no . '%');
        }
        if (!empty($this->category_id)) {
            $query->where('category_id', $this->category_id);
        }

        return $query;
    }

    public function render()
    {
        $query = $this->buildQuery();

        return view('livewire.stocks.stocks', [
            'categories' => Category::all(),
            'products' => $query->orderBy('created_at', 'desc')->paginate(50),
        ]);
    }
}
