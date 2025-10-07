<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Product;
use App\Services\BarcodeService;
use App\Services\BarcodePdfService;
use App\Services\StockService;
use App\Models\Shop;

class Stocks extends Component
{
    use WithPagination;

    public $name;
    public $sku;
    public $model_no;
    public $category_id = '';
    public $stock_add_date = '';
    public $filter_type = 'day';

    public $productIds = [];
    public $isSelectedAll = false;

    public function pdfStocks() {
        $query = $this->buildQuery();

        $service = new StockService();

        $data = [
            'shop' => Shop::first(),
            'products' => $query->get(),
            'stock_date' => $this->stock_add_date,
            'stock_date_filter_type' => $this->filter_type,
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

    public function pdfBarcodeOneColumn() {
        $ids = $this->getSelectedProductIds();
        $barcodeService = new BarcodeService();

        $url = $barcodeService->generateBarcode([
            'sku_array' => Product::whereIn('id', $ids)->pluck('sku')->toArray()
        ]);

        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function pdfBarcodeTwoColumn()
    {
        $ids = $this->getSelectedProductIds();

        if (count($ids) % 2 !== 0) {
            return session()->flash('failed', 'Please select even items.');
        }

        $barcodeService = new BarcodePdfService();
        $pdf = $barcodeService->generateMultiplePdf($ids);

        $filePath = public_path('barcodes/barcode-labels-multiple.pdf');
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0775, true);
        }

        file_put_contents($filePath, $pdf->output());

        $url = asset('barcodes/barcode-labels-multiple.pdf');
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printBarcodeTwoColumn()
    {
        $ids = $this->getSelectedProductIds();

        if (count($ids) % 2 !== 0) {
            return session()->flash('failed', 'Please select even items.');
        }

        $barcodeService = new BarcodePdfService();
        $pdf = $barcodeService->generateMultiplePdf($ids);
        $barcodeService->printPdfWin($pdf);
        // $barcodeService->printPdfMac($pdf);

        return session()->flash('message', 'Sent to printer....');
    }

    public function selectedAll($ids) {
        $this->isSelectedAll = !$this->isSelectedAll;

        if (!$this->isSelectedAll) {
            $this->productIds = collect($this->productIds)->map(fn() => false)->toArray();
            return;
        }

        $this->productIds = $ids;
    }

    private function getSelectedProductIds() {
        return array_keys(array_filter($this->productIds));
    }

    public function printBarcode2() {
        $query = $this->buildQuery();

        $barcodeService = new BarcodeService();
        $url = $barcodeService->generateBarcode2([
            'sku_array' => $query->get()->pluck('sku')->toArray()
        ]);

        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function clearFilter() {
        $this->reset();
    }

    public function delete($id) {
        if (!auth()->user()->hasPermissionTo('stock:delete')) {
            session()->flash('failed', 'Access denied!');
            return;
        }

        $p = Product::find($id);

        session()->flash('success', 'Product deleted. ' . $p->name);
        $p->delete();
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
        if (!empty($this->stock_add_date)) {
            $date = \Carbon\Carbon::parse($this->stock_add_date);
        
            $query->when($this->filter_type == 'year', function ($q) use ($date) {
                $q->whereYear('created_at', $date->year);
            });
        
            $query->when($this->filter_type == 'month', function ($q) use ($date) {
                $q->whereYear('created_at', $date->year)
                  ->whereMonth('created_at', $date->month);
            });
        
            $query->when($this->filter_type == 'day', function ($q) use ($date) {
                $q->whereDate('created_at', $date->toDateString());
            });
        }

        return $query;
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('stock:read')) {
            return view('includes.unauthorized');
        }

        $query = $this->buildQuery();

        return view('livewire.stocks.stocks', [
            'categories' => Category::all(),
            'products' => $query->orderBy('created_at', 'desc')->paginate(50),
        ]);
    }
}
