<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use App\Models\Sale;
use App\Models\Shop;
use App\Services\SaleService;
use App\Services\PrintService;

class SaleShow extends Component
{
    public $sale_id;

    public function mount($sale_id) {
        $this->sale_id = $sale_id;
    }

    public function printSaleReceipt() {
        $s = new SaleService();
        $url = $s->generateSaleReceipt($this->sale_id);
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printSaleReceiptPos() {
        $sale = Sale::findOrFail($this->sale_id);
        $printService = new PrintService();
        $msg = $printService->printReceipt($sale);

        try {
            $s = new SaleService();
            $url = $s->generateSaleReceipt($this->sale_id);
            $this->dispatch('print-pdf', ['url' => $url]);
        } catch (\Throwable $e) {
            // Ignore preview error
        }

        session()->flash('message', $msg);
    }

    public function printSaleReceiptA4() {
        $s = new SaleService();
        $url = $s->generateSaleReceiptA4($this->sale_id);

        $printerManager = app(\App\Services\PrinterManagerService::class);
        $a4Settings = $printerManager->getSettings()['a4'];
        if (!empty($a4Settings['auto_print'])) {
            $sale = Sale::with(['saleItems.product', 'saleItems.productVariation', 'saleItems.warranty', 'customer', 'createdBy'])->findOrFail($this->sale_id);
            $result = $printerManager->printA4Document(public_path('sales/sale_receipt_a4.pdf'), null,
                view('prints.sale-receipt-a4', ['sale' => $sale, 'shop' => Shop::first()])->render());
            session()->flash($result['success'] ? 'message' : 'failed', $result['message']);
            if ($result['success']) {
                return;
            }
        }

        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('sales:read')) {
            return view('includes.unauthorized');
        }

        $sale = Sale::with([
            'saleItems.product',
            'saleItems.productVariation',
            'saleItems.warranty',
            'customer',
            'createdBy',
            'updatedBy',
            'cashRegisterSession'
        ])->findOrFail($this->sale_id);

        return view('livewire.sales.sale-show', [
            'sale' => $sale,
            'shop' => Shop::first(),
        ]);
    }
}
