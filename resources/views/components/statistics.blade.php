<div class="row g-3 mb-4">
    {{-- Card 1: Today's Sales & Orders --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">{{ __("Today's Revenue") }}</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                        <i class="fa-solid fa-arrow-trend-up me-1"></i>{{ __(':count Orders', ['count' => $todaySalesCount]) }}
                    </span>
                </div>
                <h3 class="fs-4 fw-bold text-dark mb-1">
                    {{ $currencySymbol }}{{ number_format($todayRevenue, 2) }}
                </h3>
                <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top">
                    <span>{{ __("Today's Profit") }}:</span>
                    <span class="fw-bold text-success">{{ $currencySymbol }}{{ number_format($todayProfit, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Month Revenue --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">{{ __('This Month Sales') }}</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 small">
                        {{ now()->format('F Y') }}
                    </span>
                </div>
                <h3 class="fs-4 fw-bold text-dark mb-1">
                    {{ $currencySymbol }}{{ number_format($monthRevenue, 2) }}
                </h3>
                <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top">
                    <span>{{ __('Sales History') }}:</span>
                    <a href="{{ route('sales.index') }}" class="text-primary text-decoration-none fw-semibold">
                        {{ __('View All') }} <i class="fa-solid fa-chevron-right ms-1 small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 3: Cash Register Status --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">{{ __('Cash Drawer') }}</span>
                    @if ($overdueSession)
                        <span class="badge bg-danger px-2 py-1 small">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ __('Unclosed Past Day') }}
                        </span>
                    @elseif ($openSession)
                        <span class="badge bg-success px-2 py-1 small">
                            <i class="fa-solid fa-circle-check me-1"></i>{{ __('OPEN') }}
                        </span>
                    @else
                        <span class="badge bg-secondary px-2 py-1 small">{{ __('NOT OPENED') }}</span>
                    @endif
                </div>
                <h3 class="fs-4 fw-bold text-dark mb-1">
                    @if ($openSession)
                        {{ $currencySymbol }}{{ number_format($expectedCash, 2) }}
                    @else
                        <span class="text-muted fs-6">{{ __('Drawer Closed') }}</span>
                    @endif
                </h3>
                <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top">
                    <span>{{ $openSession ? __('Expected in drawer') : __('Today float required') }}:</span>
                    <a href="{{ route('cashRegister') }}" class="text-primary text-decoration-none fw-semibold">
                        {{ __('Manage') }} <i class="fa-solid fa-chevron-right ms-1 small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 4: Inventory & Alerts --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase">{{ __('Inventory & Alerts') }}</span>
                    @if ($lowStockCount > 0)
                        <span class="badge bg-warning text-dark px-2 py-1 small">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $lowStockCount }} {{ __('Low Stock') }}
                        </span>
                    @else
                        <span class="badge bg-success px-2 py-1 small">{{ __('Stock Healthy') }}</span>
                    @endif
                </div>
                <h3 class="fs-4 fw-bold text-dark mb-1">
                    {{ $totalProducts }} <span class="fs-6 fw-normal text-muted">{{ __('Products') }}</span>
                </h3>
                <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top">
                    <span>{{ __('Pending Warranty') }}: <strong class="text-dark">{{ $pendingWarranties }}</strong></span>
                    <a href="{{ route('stocks.index') }}" class="text-primary text-decoration-none fw-semibold">
                        {{ __('Stocks') }} <i class="fa-solid fa-chevron-right ms-1 small"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
