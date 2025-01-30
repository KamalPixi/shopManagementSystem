<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use App\Services\SaleService;


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

    public function printSaleReceipt() {
        $s = new SaleService();
        $url = $s->generateSaleReceipt(1);
        
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function render()
    {
        $query = Sale::query();
        
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

        return view('livewire.sales.sales', [
            'sales' => $query->paginate(50)
        ]);
    }
}
