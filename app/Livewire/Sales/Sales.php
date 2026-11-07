<?php

namespace App\Livewire\Sales;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\PaymentMethod;
use App\Models\Category;
use App\Models\SaleItem;
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
    public $category_filter = '';
    public $show_category_breakdown = false;

    public function mount() {
        $this->setFilterToday();
    }

    public function clearFilter() {
        $this->setFilterToday();
        $this->receipt_search = '';
        $this->staff_id = '';
        $this->payment_method_filter = '';
        $this->category_filter = '';
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
        $selectedCategoryName = !empty($this->category_filter)
            ? Category::find($this->category_filter)?->name
            : null;

        $reportDateLabel = $this->sale_date;
        if ($this->filter_by === 'RANGE') {
            $reportDateLabel = ($this->start_date ?: 'Start') . ' to ' . ($this->end_date ?: 'Now');
        }

        $shop = Shop::first();
        $deductVat = (bool) ($shop?->settings['deduct_vat_from_profit'] ?? false);
        $gstPercent = (float) ($shop?->gst_percent ?? 0);

        $categoryBreakdown = $this->getCategoryBreakdown();

        $data = [
            'sales' => $sales,
            'shop' => $shop,
            'deduct_vat_from_profit' => $deductVat,
            'gst_percent' => $gstPercent,
            'report_date' => $reportDateLabel,
            'report_date_filter_type' => $this->filter_by,
            'report_print_date' => date('Y-m-d H:i:s'),
            'selected_staff' => $selectedStaff,
            'receipt_filter' => $this->receipt_search,
            'category_filter_name' => $selectedCategoryName,
            'category_breakdown' => $categoryBreakdown,
        ];

        $url = $s->generateSalesReport($data);
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printSaleReceipt($id, ?string $locale = null) {
        $s = new SaleService();
        $url = $s->generateSaleReceipt($id, $locale);
        
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printSaleReceiptPos($id, ?string $locale = null) {
        $sale = Sale::findOrFail($id);
        $printerManager = app(\App\Services\PrinterManagerService::class);
        $result = $printerManager->printPosReceipt($sale, $locale);

        try {
            $s = new SaleService();
            $url = $s->generateSaleReceipt($id, $locale);
            $this->dispatch('print-pdf', ['url' => $url]);
        } catch (\Throwable $e) {
            // Ignore preview error
        }

        session()->flash($result['success'] ? 'message' : 'failed', $result['message']);
    }

    public function printSaleReceiptA4($id, ?string $locale = null) {
        $s = new SaleService();
        $url = $s->generateSaleReceiptA4($id, $locale);

        $printerManager = app(\App\Services\PrinterManagerService::class);
        $a4Settings = $printerManager->getSettings()['a4'];
        if (!empty($a4Settings['auto_print'])) {
            $sale = Sale::with(['saleItems.product', 'saleItems.productVariation', 'saleItems.warranty', 'customer', 'createdBy'])->findOrFail($id);
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

    private function buildBaseSalesQuery()
    {
        $query = Sale::with(['saleItems.product', 'saleItems.productVariation', 'saleItems.category', 'customer', 'createdBy']);
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

    private function buildQuery()
    {
        $query = $this->buildBaseSalesQuery();

        if (!empty($this->category_filter)) {
            $catId = (int) $this->category_filter;
            $query->whereHas('saleItems', function ($q) use ($catId) {
                $q->where('category_id', $catId)
                  ->orWhere(function ($sub) use ($catId) {
                      $sub->whereNull('category_id')
                          ->whereHas('product', fn ($pq) => $pq->where('category_id', $catId));
                  });
            });
        }

        return $query;
    }

    public function getCategoryBreakdown(): array
    {
        $baseSaleIds = (clone $this->buildBaseSalesQuery())->pluck('id');

        $categories = Category::orderBy('name')->get();
        $breakdown = [];
        foreach ($categories as $cat) {
            $breakdown[$cat->id] = [
                'id' => $cat->id,
                'name' => $cat->name,
                'items_count' => 0,
                'total_sales' => 0.0,
                'total_cost' => 0.0,
                'net_profit' => 0.0,
                'margin' => 0.0,
                'is_selected' => (!empty($this->category_filter) && (int) $this->category_filter === (int) $cat->id),
            ];
        }

        $uncategorized = [
            'id' => 0,
            'name' => __('Uncategorized / Other'),
            'items_count' => 0,
            'total_sales' => 0.0,
            'total_cost' => 0.0,
            'net_profit' => 0.0,
            'margin' => 0.0,
            'is_selected' => false,
        ];

        $shop = Shop::first();
        $deductVat = (bool) ($shop?->settings['deduct_vat_from_profit'] ?? false);
        $gstPercent = (float) ($shop?->gst_percent ?? 0);

        if ($baseSaleIds->isNotEmpty()) {
            $saleItems = SaleItem::whereIn('sale_id', $baseSaleIds)
                ->with(['product'])
                ->get();

            foreach ($saleItems as $item) {
                $catId = $item->category_id ?: ($item->product?->category_id ?? null);
                $qty = (int) $item->quantity;
                $sales = (float) $item->total;
                $cost = (float) $item->purchase_price * $qty;
                $profit = $item->getCalculatedProfit($deductVat, $gstPercent);

                if ($catId && isset($breakdown[$catId])) {
                    $breakdown[$catId]['items_count'] += $qty;
                    $breakdown[$catId]['total_sales'] += $sales;
                    $breakdown[$catId]['total_cost'] += $cost;
                    $breakdown[$catId]['net_profit'] += $profit;
                } else {
                    $uncategorized['items_count'] += $qty;
                    $uncategorized['total_sales'] += $sales;
                    $uncategorized['total_cost'] += $cost;
                    $uncategorized['net_profit'] += $profit;
                }
            }
        }

        $totals = [
            'items_count' => 0,
            'total_sales' => 0.0,
            'total_cost' => 0.0,
            'net_profit' => 0.0,
            'margin' => 0.0,
        ];

        foreach ($breakdown as &$row) {
            $row['margin'] = $row['total_sales'] > 0
                ? round(($row['net_profit'] / $row['total_sales']) * 100, 1)
                : 0.0;

            $totals['items_count'] += $row['items_count'];
            $totals['total_sales'] += $row['total_sales'];
            $totals['total_cost'] += $row['total_cost'];
            $totals['net_profit'] += $row['net_profit'];
        }
        unset($row);

        if ($uncategorized['items_count'] > 0) {
            $uncategorized['margin'] = $uncategorized['total_sales'] > 0
                ? round(($uncategorized['net_profit'] / $uncategorized['total_sales']) * 100, 1)
                : 0.0;

            $totals['items_count'] += $uncategorized['items_count'];
            $totals['total_sales'] += $uncategorized['total_sales'];
            $totals['total_cost'] += $uncategorized['total_cost'];
            $totals['net_profit'] += $uncategorized['net_profit'];

            $breakdown['uncategorized'] = $uncategorized;
        }

        $totals['margin'] = $totals['total_sales'] > 0
            ? round(($totals['net_profit'] / $totals['total_sales']) * 100, 1)
            : 0.0;

        $selectedCategory = null;
        if (!empty($this->category_filter)) {
            $catId = (int) $this->category_filter;
            $selectedCategory = $breakdown[$catId] ?? null;
        }

        return [
            'rows' => $breakdown,
            'totals' => $totals,
            'selected' => $selectedCategory,
        ];
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
        $categoryBreakdown = $this->getCategoryBreakdown();

        return view('livewire.sales.sales', [
            'sales' => $query->paginate(50),
            'users' => User::all(),
            'categories' => Category::orderBy('name')->get(),
            'categoryBreakdown' => $categoryBreakdown,
            'shop' => Shop::first(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
