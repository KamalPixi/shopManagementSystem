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

    public function printSaleReceipt(?string $locale = null) {
        $s = new SaleService();
        $url = $s->generateSaleReceipt($this->sale_id, $locale);
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printSaleReceiptPos(?string $locale = null) {
        $sale = Sale::findOrFail($this->sale_id);
        $printerManager = app(\App\Services\PrinterManagerService::class);
        $result = $printerManager->printPosReceipt($sale, $locale);

        try {
            $s = new SaleService();
            $url = $s->generateSaleReceipt($this->sale_id, $locale);
            $this->dispatch('print-pdf', ['url' => $url]);
        } catch (\Throwable $e) {
            // Ignore preview error
        }

        session()->flash($result['success'] ? 'message' : 'failed', $result['message']);
    }

    public function printSaleReceiptA4(?string $locale = null) {
        $s = new SaleService();
        $url = $s->generateSaleReceiptA4($this->sale_id, $locale);

        $printerManager = app(\App\Services\PrinterManagerService::class);
        $a4Settings = $printerManager->getSettings()['a4'];
        if (!empty($a4Settings['auto_print'])) {
            $sale = Sale::with(['saleItems.product', 'saleItems.productVariation', 'saleItems.warranty', 'customer', 'createdBy'])->findOrFail($this->sale_id);
            $targetLocale = in_array($locale, ['en', 'fr'], true) ? $locale : app()->getLocale();
            $previousLocale = app()->getLocale();
            app()->setLocale($targetLocale);
            try {
                $html = view('prints.sale-receipt-a4', ['sale' => $sale, 'shop' => Shop::first()])->render();
            } finally {
                app()->setLocale($previousLocale);
            }
            $result = $printerManager->printA4Document(public_path('sales/sale_receipt_a4.pdf'), null, $html);
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
