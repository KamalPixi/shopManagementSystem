<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use App\Models\Shop;
use App\Services\SaleService;
use App\Services\PrintService;


class Sales extends Component
{
    use WithPagination;

    public $sale_date;
    public $filter_by;

    public function mount() {
        $this->setFilterToday();
    }

    public function clearFilter() {
        $this->setFilterToday();
    }

    protected function setFilterToday() {
        $this->sale_date = date('Y-m-d');
        $this->filter_by = 'DAY';
    }

    public function printSalesReport() {
        $query = $this->buildQuery();

        $s = new SaleService();
        
        $data = [
            'sales' => $query->get(),
            'shop' => Shop::first(),
            'report_date' => $this->sale_date,
            'report_date_filter_type' => $this->filter_by,
            'report_print_date' => date('Y-m-d'),
        ];

        $url = $s->generateSalesReport($data);
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printSaleReceipt($id) {
        $s = new SaleService();
        $url = $s->generateSaleReceipt($id);
        
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printSaleReceiptPos($id) {
        $sale = Sale::findOrFail($id);
        $printService = new PrintService();
        $msg = $printService->printReceipt($sale);
        session()->flash('message', $msg);
    }

    public function printSaleReceiptA4($id) {
        $s = new SaleService();
        $url = $s->generateSaleReceiptA4($id);
        
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    private function buildQuery()
    {
        $query = Sale::query();
        $query->orderBy('created_at', 'desc');
        
        if ($this->sale_date && $this->filter_by) {
            $date = \Carbon\Carbon::parse($this->sale_date);

            switch ($this->filter_by) {
                case 'DAY':
                    $query->whereDate('created_at', $date->format('Y-m-d'));
                    break;

                case 'MONTH':
                    $query->whereMonth('created_at', $date->format('m'))->whereYear('created_at', $date->format('Y'));
                    break;

                case 'YEAR':
                    $query->whereYear('created_at', $date->format('Y'));
                    break;
            }
        }

        return $query;
    }

    public function deleteSale($id) {
        if (!auth()->user()->hasPermissionTo('sales:delete')) {
            return view('includes.unauthorized');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $sale = Sale::with('saleItems')->findOrFail($id);

            // Restore product stock for all catalog items in this sale
            foreach ($sale->saleItems as $item) {
                if (!empty($item->product_id)) {
                    $product = \App\Models\Product::find($item->product_id);
                    if ($product) {
                        $product->increment('quantity', (int) $item->quantity);
                    }
                }
            }

            $sale->saleItems()->delete();
            $sale->delete();
        });

        session()->flash('success', 'Sale has been deleted and product quantities have been returned to stock.');
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('sales:read')) {
            return view('includes.unauthorized');
        }

        $query = $this->buildQuery();

        return view('livewire.sales.sales', [
            'sales' => $query->paginate(50)
        ]);
    }
}
