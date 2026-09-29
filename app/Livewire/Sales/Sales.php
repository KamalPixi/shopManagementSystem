<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\SaleService;
use App\Services\PrintService;
use App\Services\AuditLogger;

class Sales extends Component
{
    use WithPagination;

    public $sale_date;
    public $filter_by;

    // Enhanced Filters
    public $receipt_search = '';
    public $staff_id = '';
    public $start_date = '';
    public $end_date = '';
    public $payment_method_filter = '';

    public function mount() {
        $this->setFilterToday();
    }

    public function clearFilter() {
        $this->setFilterToday();
        $this->receipt_search = '';
        $this->staff_id = '';
        $this->payment_method_filter = '';
        $this->start_date = '';
        $this->end_date = '';
    }

    protected function setFilterToday() {
        $this->sale_date = date('Y-m-d');
        $this->filter_by = 'DAY';
    }

    public function printSalesReport() {
        $query = $this->buildQuery();
        $sales = $query->get();

        $s = new SaleService();

        $selectedStaff = !empty($this->staff_id) ? User::find($this->staff_id)?->name : null;

        $reportDateLabel = $this->sale_date;
        if ($this->filter_by === 'RANGE') {
            $reportDateLabel = ($this->start_date ?: 'Start') . ' to ' . ($this->end_date ?: 'Now');
        }

        $data = [
            'sales' => $sales,
            'shop' => Shop::first(),
            'report_date' => $reportDateLabel,
            'report_date_filter_type' => $this->filter_by,
            'report_print_date' => date('Y-m-d H:i:s'),
            'selected_staff' => $selectedStaff,
            'receipt_filter' => $this->receipt_search,
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

        try {
            $s = new SaleService();
            $url = $s->generateSaleReceipt($id);
            $this->dispatch('print-pdf', ['url' => $url]);
        } catch (\Throwable $e) {
            // Ignore preview error
        }

        session()->flash('message', $msg);
    }

    public function printSaleReceiptA4($id) {
        $s = new SaleService();
        $url = $s->generateSaleReceiptA4($id);
        
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    private function buildQuery()
    {
        $query = Sale::with(['saleItems.product', 'saleItems.productVariation', 'customer', 'createdBy']);
        $query->orderBy('created_at', 'desc');

        // Filter by Unique Receipt Number (e.g. TP-000001 or partial numbers)
        if (!empty($this->receipt_search)) {
            $term = trim($this->receipt_search);
            $query->where(function ($q) use ($term) {
                $q->where('receipt_no', 'like', '%' . $term . '%');
                if (is_numeric($term)) {
                    $q->orWhere('receipt_no', 'like', '%-' . $term)
                      ->orWhere('id', (int) $term);
                }
            });
        }

        // Filter by Staff
        if (!empty($this->staff_id)) {
            $query->where('created_by', $this->staff_id);
        }

        // Filter by Payment Method
        if (!empty($this->payment_method_filter)) {
            $query->where('payment_method', $this->payment_method_filter);
        }

        // Filter by Date Range or Quick Date
        if ($this->filter_by === 'RANGE') {
            if (!empty($this->start_date)) {
                $query->whereDate('created_at', '>=', $this->start_date);
            }
            if (!empty($this->end_date)) {
                $query->whereDate('created_at', '<=', $this->end_date);
            }
        } elseif ($this->sale_date && $this->filter_by) {
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
            $receiptNo = $sale->receipt_no;
            $totalAmount = $sale->total_price;

            // Restore product and variation stock
            foreach ($sale->saleItems as $item) {
                if (!empty($item->product_variation_id)) {
                    $variation = \App\Models\ProductVariation::find($item->product_variation_id);
                    if ($variation) {
                        $variation->increment('quantity', (int) $item->quantity);
                    }
                }
                if (!empty($item->product_id)) {
                    $product = \App\Models\Product::find($item->product_id);
                    if ($product) {
                        $product->increment('quantity', (int) $item->quantity);
                    }
                }
            }

            $sale->saleItems()->delete();
            $sale->delete();

            AuditLogger::log(
                'deleted_sale',
                __(':user deleted sale :receipt of :amount (Stock returned to inventory)', [
                    'user' => auth()->user()->name,
                    'receipt' => $receiptNo,
                    'amount' => number_format($totalAmount, 2)
                ]),
                null,
                ['receipt_no' => $receiptNo, 'total_price' => $totalAmount]
            );
        });

        session()->flash('success', __('Sale has been deleted and product quantities have been returned to stock.'));
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('sales:read')) {
            return view('includes.unauthorized');
        }

        $query = $this->buildQuery();

        return view('livewire.sales.sales', [
            'sales' => $query->paginate(50),
            'users' => User::all(),
            'shop' => Shop::first(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
