<div>
    @include('includes.breadcrumb', ['data' => ['Sales' => route('sales.index')]])

    <div wire:loading.flex>
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp

    {{-- Filter Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <h5 class="h6 mb-3 fw-bold">{{ __('Filter & Search Sales') }}</h5>
                    <div class="row g-2 mb-3">
                        {{-- Find Sale by Unique Receipt Number (TP-000000) --}}
                        <div class="col-md-2 col-12">
                            <label class="small text-muted mb-1">{{ __('Receipt Number') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fa-solid fa-receipt"></i></span>
                                <input wire:model.live.debounce.300ms="receipt_search" type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. TP-000001 or 000001">
                            </div>
                        </div>

                        {{-- Filter by Staff --}}
                        <div class="col-md-2 col-6">
                            <label class="small text-muted mb-1">{{ __('Sold By (Staff)') }}</label>
                            <select wire:model.live="staff_id" class="form-select form-select-sm">
                                <option value="">{{ __('All Staff') }}</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Payment Method Filter --}}
                        <div class="col-md-2 col-6">
                            <label class="small text-muted mb-1">{{ __('Payment Method') }}</label>
                            <select wire:model.live="payment_method_filter" class="form-select form-select-sm">
                                <option value="">{{ __('All Methods') }}</option>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->code }}">{{ __($method->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Category Filter --}}
                        <div class="col-md-2 col-6">
                            <label class="small text-muted mb-1"><i class="fa-solid fa-tags me-1 text-primary"></i>{{ __('Category') }}</label>
                            <select wire:model.live="category_filter" class="form-select form-select-sm">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date Filter Type --}}
                        <div class="col-md-2 col-6">
                            <label class="small text-muted mb-1">{{ __('Date Filter Type') }}</label>
                            <select wire:model.live="filter_by" class="form-select form-select-sm">
                                <option value="DAY">{{ __('Day') }}</option>
                                <option value="MONTH">{{ __('Month') }}</option>
                                <option value="YEAR">{{ __('Year') }}</option>
                                <option value="RANGE">{{ __('Custom Date Range') }}</option>
                            </select>
                        </div>

                        {{-- Date Pickers --}}
                        @if ($filter_by === 'RANGE')
                            <div class="col-md-1 col-6">
                                <label class="small text-muted mb-1">{{ __('From') }}</label>
                                <input wire:model.live="start_date" type="date" class="form-control form-control-sm px-1">
                            </div>
                            <div class="col-md-1 col-6">
                                <label class="small text-muted mb-1">{{ __('To') }}</label>
                                <input wire:model.live="end_date" type="date" class="form-control form-control-sm px-1">
                            </div>
                        @else
                            <div class="col-md-2 col-6">
                                <label class="small text-muted mb-1">{{ __('Select Date') }}</label>
                                <input wire:model.live="sale_date" type="date" class="form-control form-control-sm">
                            </div>
                        @endif
                    </div>

                    <div class="text-end">
                        <button wire:click="clearFilter" class="btn btn-sm btn-outline-danger px-4">
                            <i class="fa-solid fa-rotate-left me-1"></i> {{ __('Reset Filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Performance Breakdown Section --}}
    @php
        $selectedCat = $categoryBreakdown['selected'] ?? null;
    @endphp

    @if ($selectedCat)
        {{-- Highlighted Category Summary KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="alert alert-primary border-primary shadow-sm d-flex flex-wrap justify-content-between align-items-center py-2 px-3 mb-0">
                    <div>
                        <i class="fa-solid fa-filter me-2 fs-6"></i>
                        <span class="fw-bold">{{ __('Filtered by Category:') }}</span>
                        <span class="badge bg-primary fs-6 ms-1">{{ $selectedCat['name'] }}</span>
                        <span class="small text-muted ms-2">({{ $selectedCat['items_count'] }} {{ __('items sold') }})</span>
                    </div>
                    <button wire:click="$set('category_filter', '')" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-xmark me-1"></i>{{ __('Clear Category Filter') }}
                    </button>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $selectedCat['name'] }} {{ __('Sales') }}</small>
                        <h4 class="fw-bold text-primary mb-0 mt-1">{{ $currencySymbol }}{{ number_format($selectedCat['total_sales'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $selectedCat['name'] }} {{ __('Cost') }}</small>
                        <h4 class="fw-bold text-danger mb-0 mt-1">{{ $currencySymbol }}{{ number_format($selectedCat['total_cost'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $selectedCat['name'] }} {{ __('Net Profit') }}</small>
                        <h4 class="fw-bold text-success mb-0 mt-1">{{ $currencySymbol }}{{ number_format($selectedCat['net_profit'], 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm bg-white">
                    <div class="card-body p-3">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $selectedCat['name'] }} {{ __('Margin') }}</small>
                        <h4 class="fw-bold text-info mb-0 mt-1">{{ $selectedCat['margin'] }}%</h4>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Category Performance Breakdown Table Card (Collapsible, default collapsed) --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center"
                 wire:click="$toggle('show_category_breakdown')"
                 style="cursor: pointer; user-select: none;">
                <div class="d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-chart-pie" style="font-size: 0.85rem;"></i>
                    </div>
                    <div>
                        <h5 class="h6 mb-0 fw-bold d-flex align-items-center gap-2">
                            <span>{{ __('Category Sales & Profit Breakdown') }}</span>
                            @if (!$show_category_breakdown)
                                <span class="badge bg-secondary-subtle text-muted fw-normal" style="font-size: 0.68rem;">
                                    <i class="fa-solid fa-chevron-down me-1"></i>{{ __('Click to expand') }}
                                </span>
                            @endif
                        </h5>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ __('Sales amount, cost and net profit categorized for this period') }}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2" onclick="event.stopPropagation()">
                    @if (!empty($category_filter))
                        <button type="button" wire:click="$set('category_filter', '')" class="btn btn-xs btn-outline-secondary" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-list me-1"></i>{{ __('Show All Categories') }}
                        </button>
                    @endif
                    <button type="button"
                            wire:click="$toggle('show_category_breakdown')"
                            class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1 py-1 px-2.5"
                            style="font-size: 0.78rem;">
                        @if ($show_category_breakdown)
                            <i class="fa-solid fa-chevron-up"></i>
                            <span>{{ __('Collapse') }}</span>
                        @else
                            <i class="fa-solid fa-chevron-down"></i>
                            <span>{{ __('Expand') }}</span>
                        @endif
                    </button>
                </div>
            </div>

            @if ($show_category_breakdown)
                <div class="table-responsive mt-2 pt-2 border-top">
                    <table class="table table-sm table-hover table-centered mb-0 align-middle" style="font-size: 0.8rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 px-2 py-1">{{ __('Category') }}</th>
                                <th class="border-0 text-center py-1">{{ __('Items Sold') }}</th>
                                <th class="border-0 text-end py-1">{{ __('Sales Amount') }}</th>
                                <th class="border-0 text-end py-1">{{ __('Cost Price') }}</th>
                                <th class="border-0 text-end py-1">{{ __('Net Profit') }}</th>
                                <th class="border-0 text-end py-1 px-2">{{ __('Margin') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categoryBreakdown['rows'] as $row)
                                @php
                                    $isRowSelected = !empty($row['is_selected']);
                                @endphp
                                <tr class="{{ $isRowSelected ? 'table-primary fw-bold' : '' }}" style="cursor: pointer;"
                                    wire:click="$set('category_filter', '{{ $row['id'] ? $row['id'] : '' }}')"
                                    title="{{ __('Click to filter sales by :category', ['category' => $row['name']]) }}">
                                    <td class="px-2 py-1">
                                        <div class="d-flex align-items-center">
                                            <i class="fa-solid fa-tag me-1.5 text-muted" style="font-size: 0.7rem;"></i>
                                            <span class="{{ $isRowSelected ? 'text-primary fw-bold' : 'text-dark' }}">{{ $row['name'] }}</span>
                                            @if ($isRowSelected)
                                                <span class="badge bg-primary ms-1" style="font-size: 0.62rem;">{{ __('Active Filter') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center py-1">
                                        <span class="badge bg-secondary-subtle text-dark" style="font-size: 0.72rem;">{{ $row['items_count'] }}</span>
                                    </td>
                                    <td class="text-end py-1 font-monospace fw-semibold">
                                        {{ $currencySymbol }}{{ number_format($row['total_sales'], 2) }}
                                    </td>
                                    <td class="text-end py-1 font-monospace text-muted">
                                        {{ $currencySymbol }}{{ number_format($row['total_cost'], 2) }}
                                    </td>
                                    <td class="text-end py-1 font-monospace fw-bold {{ $row['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $currencySymbol }}{{ number_format($row['net_profit'], 2) }}
                                    </td>
                                    <td class="text-end py-1 px-2 font-monospace">
                                        <span class="badge {{ $row['margin'] >= 20 ? 'bg-success-subtle text-success' : ($row['margin'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary') }} border" style="font-size: 0.72rem;">
                                            {{ $row['margin'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light border-top border-2">
                            <tr class="fw-bold">
                                <td class="px-2 py-1 text-uppercase" style="font-size: 0.72rem;">{{ __('Total / Overall') }}</td>
                                <td class="text-center py-1">
                                    <span class="badge bg-dark" style="font-size: 0.72rem;">{{ $categoryBreakdown['totals']['items_count'] }}</span>
                                </td>
                                <td class="text-end py-1 font-monospace text-primary">
                                    {{ $currencySymbol }}{{ number_format($categoryBreakdown['totals']['total_sales'], 2) }}
                                </td>
                                <td class="text-end py-1 font-monospace text-muted">
                                    {{ $currencySymbol }}{{ number_format($categoryBreakdown['totals']['total_cost'], 2) }}
                                </td>
                                <td class="text-end py-1 font-monospace text-success">
                                    {{ $currencySymbol }}{{ number_format($categoryBreakdown['totals']['net_profit'], 2) }}
                                </td>
                                <td class="text-end py-1 px-2 font-monospace">
                                    <span class="badge bg-primary text-white" style="font-size: 0.72rem;">
                                        {{ $categoryBreakdown['totals']['margin'] }}%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Sales List Table --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="h6 mb-0 fw-bold">{{ __('Sales Records') }}</h5>
                <div>
                    <button type="button" wire:click="printSalesReport" class="btn btn-sm btn-secondary">
                        <i class="fa-solid fa-print pe-2"></i>{{ __('Print Sales Report (PDF)') }}
                    </button>
                </div>
            </div>

            <div class="mb-4 table-responsive">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:2%;" class="border-0 rounded-start text-start px-2">#</th>
                            <th class="border-0">{{ __('Receipt #') }}</th>
                            <th class="border-0">{{ __('Products & Variations') }}</th>
                            <th class="border-0 text-center">{{ __('Payment') }}</th>
                            <th class="border-0 text-center">{{ __('Discount') }}</th>
                            <th class="border-0 text-center">{{ $shop?->tax_label ?? 'GST' }}</th>
                            <th class="border-0 text-center">{{ __('Grand Total') }}</th>
                            <th class="border-0 text-center">{{ __('Date & Time') }}</th>
                            <th class="border-0 text-center">{{ __('Customer') }}</th>
                            <th class="border-0 text-center">{{ __('Sold By') }}</th>
                            <th style="width: 8%;" class="border-0 text-end rounded-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                        <tr wire:key="tr_key_{{ $sale->id }}">
                            <td class="px-2 py-0 align-middle">{{ $sales->firstItem() + $loop->index }}</td>
                            
                            {{-- Receipt Number --}}
                            <td class="align-middle fw-bold font-monospace fs-6">
                                <a href="{{ route('sales.show', $sale->id) }}" class="text-dark text-decoration-none" title="{{ __('View Sale Details') }}">
                                    {{ $sale->receipt_no ?: sprintf('TP-%06d', $sale->id) }}
                                </a>
                            </td>

                            {{-- Products --}}
                            <td class="fw-bold align-items-center lh-1 align-middle px-1" style="width: 45%;"> 
                                <table class="table table-striped table-bordered table-hover w-100 mb-0">
                                    @foreach ($sale->saleItems as $item)
                                        <tr wire:key="sale_items_tr_{{$item->id}}">
                                            <td class="px-1 py-1" style="width: 45%;"> 
                                                @if ($item->product_id)
                                                    <a href="{{ route('sales.show', $sale->id) }}" class="small fw-bold text-dark text-decoration-none" title="{{ __('View Sale Details') }}">
                                                        {{ $item->name }}
                                                    </a>
                                                @else
                                                    <a href="{{ route('sales.show', $sale->id) }}" class="small fw-bold text-dark text-decoration-none" title="{{ __('View Sale Details') }}">
                                                        {{ $item->name }}
                                                    </a>
                                                    <span class="badge bg-info text-white" style="font-size: 0.6rem;">Custom</span>
                                                @endif
                                                @if ($item->is_vatable)
                                                    <span class="badge bg-success ms-1" style="font-size: 0.55rem;">VAT</span>
                                                @endif
                                                @php
                                                    $itemCatName = $item->category_name;
                                                @endphp
                                                @if ($itemCatName && $itemCatName !== 'Uncategorized')
                                                    <span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size: 0.6rem;">{{ $itemCatName }}</span>
                                                @endif
                                                @if ($item->productVariation)
                                                    <br><small class="text-primary">{{ $item->productVariation->variation_name }}</small>
                                                @endif
                                                @if ($item->product?->sku || $item->productVariation?->sku)
                                                    <br><small class="text-muted font-monospace">{{ $item->productVariation?->sku ?: $item->product?->sku }}</small>
                                                @endif
                                                @if (($item->product?->serial_no || $item->productVariation?->serial_no) && $item->show_serial_on_receipt)
                                                    <br><small class="text-muted font-monospace">SN: {{ $item->productVariation?->serial_no ?: $item->product?->serial_no }}</small>
                                                @endif
                                            </td>
                                            <td class="px-1 py-1 align-middle text-center" style="width: 10%;"> 
                                                <small class="text-muted lh-1" style="font-size: 0.62rem;">{{ __('Category') }}:</small><br>
                                                <span class="text-secondary" style="font-size: 0.68rem; line-height: 1;">{{ $item->category_name }}</span>
                                            </td>
                                            <td class="px-1 py-1 align-middle text-center" style="width: 14%;"> 
                                                <small class="text-muted lh-1">{{ __('Warranty') }}: <br> <b class="text-primary">{{$item?->warranty?->name ?? '-' }}</b></small>
                                                @if (!$item->show_warranty_on_receipt && ($item->warranty_id || $item->warranty_description))
                                                    <br><small class="text-muted" style="font-size: 0.6rem;">({{ __('Hidden on receipt') }})</small>
                                                @endif
                                            </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Qty: <br> <b class="text-primary">{{$item?->quantity }}</b></small> </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Price: <br> <b class="text-primary">{{$currencySymbol}}{{ number_format($item?->price, 2) }}</b></small> </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Subtotal: <br> <b class="text-primary">{{$currencySymbol}}{{ number_format($item?->total, 2) }}</b></small> </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>

                            {{-- Payment Method & Reference --}}
                            <td class="align-middle text-center">
                                @php
                                    $isSplit = $sale->isSplitPayment() || $sale->payments->count() > 1;
                                    $pMethod = $sale->payment_method ?: 'cash';
                                    $pBadge = 'bg-success';
                                    if ($isSplit) $pBadge = 'bg-primary text-white';
                                    elseif ($pMethod === 'card') $pBadge = 'bg-info text-white';
                                    elseif ($pMethod === 'bank_transfer') $pBadge = 'bg-warning text-dark';
                                @endphp
                                @if ($isSplit)
                                    <span class="badge {{ $pBadge }} text-uppercase" style="font-size: 0.7rem;" title="{{ $sale->payment_summary }}">
                                        <i class="fa-solid fa-layer-group me-1"></i>{{ __('Split') }}
                                    </span>
                                    <div class="mt-1" style="line-height: 1.1;">
                                        @foreach ($sale->payments as $sp)
                                            <span class="badge bg-secondary-subtle text-secondary border me-1" style="font-size: 0.6rem;">
                                                {{ $sp->payment_method }}: {{ $currencySymbol }}{{ number_format($sp->amount, 0) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="badge {{ $pBadge }} text-uppercase" style="font-size: 0.7rem;">
                                        {{ str_replace('_', ' ', $pMethod) }}
                                    </span>
                                @endif
                                @if ($sale->payment_reference)
                                    <small class="text-muted d-block mt-1 font-monospace" style="font-size: 0.7rem;" title="{{ $sale->payment_reference }}">
                                        Ref: {{ \Illuminate\Support\Str::limit($sale->payment_reference, 12) }}
                                    </small>
                                @endif
                                @if ($sale->payment_attachment)
                                    <a href="{{ asset($sale->payment_attachment) }}" target="_blank" class="small text-primary d-inline-block mt-1" title="{{ __('View Slip') }}">
                                        <i class="fa-solid fa-paperclip"></i>
                                    </a>
                                @endif
                            </td>

                            <td class="align-middle text-center fw-bold">{{ $currencySymbol }}{{ number_format($sale->discount, 2) }}</td>
                            <td class="align-middle text-center fw-bold">{{ $currencySymbol }}{{ number_format($sale->gst, 2) }}</td>
                            <td class="align-middle text-center fw-bold text-dark fs-6">{{ $currencySymbol }}{{ number_format($sale->total_price, 2) }}</td>

                            {{-- Date --}}
                            <td class="align-middle text-center lh-1"> 
                                <small class="fw-bold">{{ $sale->created_at?->format('Y-m-d') }}</small> <br>
                                <small class="text-muted">{{ $sale->created_at?->format('h:i A') }}</small>
                            </td>

                            {{-- Customer --}}
                            <td class="align-middle text-center lh-1"> 
                                <small class="fw-bold">{{ $sale->customer?->name ?: 'Walk In' }}</small>
                                @if ($sale->customer?->phone_no)
                                    <br><small class="text-muted">{{ $sale->customer->phone_no }}</small>
                                @endif
                            </td>

                            {{-- Sold By (Staff) --}}
                            <td class="align-middle text-center lh-1">
                                <span class="fw-bold text-primary">{{ $sale->createdBy?->name ?? '-' }}</span>
                                @if ($sale->updatedBy)
                                    <br><small class="text-muted" style="font-size: 0.65rem;">(Edit: {{ $sale->updatedBy->name }})</small>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end align-middle">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow py-2" style="min-width: 17.5rem;">
                                        <li>
                                            <a class="dropdown-item text-dark py-2" href="{{ route('sales.show', $sale->id) }}">
                                                <i class="fa-solid fa-eye pe-2 text-muted"></i>{{ __('View Details') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-primary py-2" href="{{ route('sales.edit', $sale->id) }}">
                                                <i class="fa-solid fa-pen-to-square pe-2"></i>{{ __('Edit Sale') }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-2"></li>

                                        <li class="dropdown-header text-uppercase text-muted fw-bold px-3 py-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                            <i class="fa-solid fa-print me-1"></i>{{ __('Print / Download') }}
                                        </li>

                                        {{-- Receipt (POS Print 80mm) --}}
                                        <li class="px-3 py-1">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <span class="small fw-semibold text-secondary text-nowrap">
                                                    <i class="fa-solid fa-print me-2 text-dark"></i>{{ __('POS (80mm)') }}
                                                </span>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-dark fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                        wire:click="printSaleReceiptPos('{{$sale->id}}', 'en')"
                                                        wire:loading.attr="disabled"
                                                        title="{{ __('English POS receipt') }}">
                                                        EN
                                                    </button>
                                                    <button type="button" class="btn btn-outline-dark fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                        wire:click="printSaleReceiptPos('{{$sale->id}}', 'fr')"
                                                        wire:loading.attr="disabled"
                                                        title="{{ __('French POS receipt') }}">
                                                        FR
                                                    </button>
                                                </div>
                                            </div>
                                        </li>

                                        {{-- Receipt (PDF 80mm) --}}
                                        <li class="px-3 py-1">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <span class="small fw-semibold text-secondary text-nowrap">
                                                    <i class="fa-solid fa-file-pdf me-2 text-danger"></i>{{ __('Receipt (PDF)') }}
                                                </span>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-danger fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                        wire:click="printSaleReceipt('{{$sale->id}}', 'en')"
                                                        wire:loading.attr="disabled"
                                                        title="{{ __('English 80mm PDF') }}">
                                                        EN
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                        wire:click="printSaleReceipt('{{$sale->id}}', 'fr')"
                                                        wire:loading.attr="disabled"
                                                        title="{{ __('French 80mm PDF') }}">
                                                        FR
                                                    </button>
                                                </div>
                                            </div>
                                        </li>

                                        {{-- Receipt A4 (PDF Invoice) --}}
                                        <li class="px-3 py-1">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <span class="small fw-semibold text-secondary text-nowrap">
                                                    <i class="fa-solid fa-file-lines me-2 text-info"></i>{{ __('Invoice (A4)') }}
                                                </span>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-info fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                        wire:click="printSaleReceiptA4('{{$sale->id}}', 'en')"
                                                        wire:loading.attr="disabled"
                                                        title="{{ __('English A4 PDF') }}">
                                                        EN
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                        wire:click="printSaleReceiptA4('{{$sale->id}}', 'fr')"
                                                        wire:loading.attr="disabled"
                                                        title="{{ __('French A4 PDF') }}">
                                                        FR
                                                    </button>
                                                </div>
                                            </div>
                                        </li>

                                        <li><hr class="dropdown-divider my-2"></li>
                                        <li>
                                            <a class="dropdown-item text-danger py-2" href="#" wire:confirm="{{ __('Are you sure you want to delete this sale? Stock will be restored.') }}" wire:click="deleteSale('{{$sale->id}}')">
                                                <i class="fa-solid fa-trash pe-2"></i>{{ __('Delete') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">{{ __('No sales found matching the criteria.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sales->links() }}
        </div>
    </div>
</div>
