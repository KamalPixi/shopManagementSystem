<div>
    @include('includes.breadcrumb', ['data' => ['Stock' => route('stocks.index'), 'View' => '']])

    {{-- Product Header Card --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h3 class="fw-bold mb-0 text-dark">{{ $product->name }}</h3>
                        @if ($product->status === 'Active')
                            <span class="badge bg-success text-white" style="font-size: 0.72rem;">{{ __('Active') }}</span>
                        @else
                            <span class="badge bg-secondary text-white" style="font-size: 0.72rem;">{{ __('Inactive') }}</span>
                        @endif

                        @if ($product->is_vatable)
                            <span class="badge bg-info text-white" style="font-size: 0.72rem;">VAT</span>
                        @endif

                        @if ($product->has_variations)
                            <span class="badge bg-primary text-white" style="font-size: 0.72rem;">{{ $product->variations->count() }} {{ __('Variations') }}</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-3 text-muted small">
                        <span>SKU: <strong class="font-monospace text-dark">{{ $product->sku }}</strong></span>
                        @if ($product->model_no)
                            <span class="text-secondary">•</span>
                            <span>Model: <strong class="text-dark">{{ $product->model_no }}</strong></span>
                        @endif
                        @if ($product->serial_no)
                            <span class="text-secondary">•</span>
                            <span>SN/IMEI: <strong class="font-monospace text-dark">{{ $product->serial_no }}</strong></span>
                        @endif
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('stocks.edit', $product->id) }}" class="btn btn-sm btn-primary shadow-sm px-3">
                        {{ __('Edit Product') }}
                    </a>
                    <a href="{{ route('stocks.addToStock') }}?product_id={{ $product->id }}" class="btn btn-sm btn-outline-success shadow-sm px-3">
                        {{ __('Add Stock') }}
                    </a>
                    <a href="{{ route('stocks.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                        {{ __('Back to Stock') }}
                    </a>
                </div>
            </div>

            {{-- Summary Metric Highlights --}}
            <div class="row g-2 mt-2 pt-2 border-top">
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center h-100">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">{{ __('Total Stock') }}</span>
                        <div class="fs-5 fw-bold {{ $product->is_low_stock ? 'text-danger' : 'text-dark' }}">
                            {{ $product->quantity }}
                            @if ($product->is_low_stock)
                                <span class="badge bg-danger text-white ms-1 align-middle" style="font-size: 0.6rem;">{{ __('Low') }}</span>
                            @endif
                        </div>
                        <small class="text-muted" style="font-size: 0.68rem;">{{ __('Min alert: :thresh', ['thresh' => $product->effective_low_stock_threshold]) }}</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center h-100">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">{{ __('Sale Price') }}</span>
                        <div class="fs-5 fw-bold text-dark font-monospace">
                            @if ($product->has_variations && $product->variations->count() > 0)
                                €{{ number_format((float) $product->variations->sum('price'), 2) }}
                            @else
                                €{{ number_format((float) $product->price, 2) }}
                            @endif
                        </div>
                        <small class="text-muted" style="font-size: 0.68rem;">
                            {{ $product->has_variations ? __('(sum of all variations)') : __('(per unit)') }}
                        </small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center h-100">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">{{ __('Cost / Purchase Price') }}</span>
                        <div class="fs-5 fw-bold text-secondary font-monospace">
                            €{{ number_format((float) $product->purchase_price, 2) }}
                        </div>
                        <small class="text-muted" style="font-size: 0.68rem;">{{ __('Base unit cost') }}</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center h-100">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">{{ __('Total Units Sold') }}</span>
                        <div class="fs-5 fw-bold text-primary font-monospace">
                            {{ $product->saleItems->sum('quantity') }}
                        </div>
                        <small class="text-muted" style="font-size: 0.68rem;">{{ __('in :count orders', ['count' => $sales->count()]) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Specifications & Configuration Details --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-white border-bottom py-2 px-3">
            <h6 class="fw-bold mb-0 text-dark">
                {{ __('Product Specifications') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-muted py-2" style="width: 35%;">{{ __('Category') }}</td>
                                <td class="fw-semibold py-2">{{ $product->category?->name ?? '-' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">{{ __('Brand') }}</td>
                                <td class="fw-semibold py-2">{{ $product->brand?->name ?? '-' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">{{ __('Warranty') }}</td>
                                <td class="fw-semibold py-2">
                                    {{ $product->warranty?->name ?? '-' }}
                                    @if ($product->warranty?->description)
                                        <small class="text-muted d-block">{{ $product->warranty->description }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">{{ __('Description') }}</td>
                                <td class="py-2">{{ $product->description ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-12 col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-muted py-2" style="width: 35%;">{{ __('Product Type') }}</td>
                                <td class="fw-semibold py-2">
                                    {{ $product->has_variations ? __('Configurable (Variations)') : __('Standard Single Product') }}
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">{{ __('Attributes') }}</td>
                                <td class="py-2">
                                    @if (collect($product->attributes_and_values)->filter(fn($p) => !empty($p['name']))->count())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($product->attributes_and_values ?? [] as $att_value)
                                                <span class="badge bg-light text-dark border">
                                                    {{ ucfirst($att_value['name']) }}: <strong>{{ implode(', ', $att_value['values']) }}</strong>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">{{ __('Tax / TVA') }}</td>
                                <td class="fw-semibold py-2">
                                    @if ($product->is_vatable)
                                        <span class="text-success fw-bold">{{ __('Subject to VAT') }}</span>
                                    @else
                                        <span class="text-muted">{{ __('Non-VAT') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">{{ __('Remark') }}</td>
                                <td class="py-2">{{ $product->remark ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Variation Inventory Table (If Product Has Variations) --}}
    @if ($product->has_variations)
        <div class="card border-0 shadow mb-4">
            <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    {{ __('Variation Inventory') }}
                    <span class="badge bg-primary text-white ms-2" style="font-size: 0.7rem;">{{ $product->variations->count() }} {{ __('variants') }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered table-nowrap align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="border-0 rounded-start text-start ps-3">{{ __('Variation') }}</th>
                                <th class="border-0">{{ __('SKU') }}</th>
                                <th class="border-0">{{ __('Serial / IMEI') }}</th>
                                <th class="border-0 text-end">{{ __('Sale Price') }}</th>
                                <th class="border-0 text-end">{{ __('Cost Price') }}</th>
                                <th class="border-0 text-center">{{ __('Stock') }}</th>
                                <th class="border-0 text-center rounded-end pe-3">{{ __('Low Alert') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($product->variations as $variation)
                                <tr>
                                    <td class="text-start ps-3 fw-bold text-dark">
                                        <span class="badge bg-primary text-white me-1">{{ $variation->variation_name }}</span>
                                    </td>
                                    <td class="font-monospace">{{ $variation->sku ?: '-' }}</td>
                                    <td class="font-monospace text-muted">{{ $variation->serial_no ?: '-' }}</td>
                                    <td class="text-end fw-bold font-monospace text-dark">€{{ number_format((float) $variation->price, 2) }}</td>
                                    <td class="text-end font-monospace text-muted">€{{ number_format((float) $variation->purchase_price, 2) }}</td>
                                    <td class="text-center">
                                        @if ($variation->quantity <= ($variation->low_stock_threshold ?? $product->effective_low_stock_threshold))
                                            <span class="badge bg-danger text-white py-1 px-2">
                                                {{ $variation->quantity }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark border py-1 px-2 fw-bold">{{ $variation->quantity }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3 text-muted">
                                        {{ $variation->low_stock_threshold ?? $product->effective_low_stock_threshold }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">{{ __('No variations configured.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Stock Add / Purchase History --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-white border-bottom py-2 px-3">
            <h6 class="fw-bold mb-0 text-dark">
                {{ __('Stock Add / Purchase History') }}
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered table-nowrap align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 rounded-start text-center ps-3" style="width: 3%;">#</th>
                            <th class="border-0 text-center">{{ __('Purchase Date') }}</th>
                            <th class="border-0 text-start">{{ __('Attributes & Values') }}</th>
                            <th class="border-0 text-end">{{ __('Purchase Price') }}</th>
                            <th class="border-0 text-end">{{ __('Sale Price') }}</th>
                            <th class="border-0 text-center">{{ __('Qty Purchased') }}</th>
                            <th class="border-0 text-center">{{ __('Supplier') }}</th>
                            <th class="border-0 text-center">{{ __('Payment Method') }}</th>
                            <th class="border-0 text-center rounded-end pe-3">{{ __('Added By') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->productPurchases as $p)
                        <tr>
                            <td class="text-center ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td class="text-center font-monospace">{{ $p->purchase_date?->format('Y-m-d') ?: '-' }}</td>
                            <td class="text-start">
                                @if (collect($product->attributes_and_values)->filter(fn($val) => !empty($val['name']))->count())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($product->attributes_and_values ?? [] as $att_value)
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">
                                                {{ ucfirst($att_value['name']) }}: <strong>{{ implode(', ', $att_value['values']) }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace text-muted">€{{ number_format((float) $p->purchase_price, 2) }}</td>
                            <td class="text-end font-monospace fw-bold text-dark">€{{ number_format((float) $p->sale_price, 2) }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border fw-bold">{{ $p->quantity }}</span>
                            </td>
                            <td class="text-center text-muted">{{ $p->supplier?->name ?? '-' }}</td>
                            <td class="text-center text-muted">{{ $p->paymentMethod?->name ?? '-' }}</td>
                            <td class="text-center pe-3 text-muted">{{ $p->createdBy?->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">{{ __('No purchase history available.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- Sales History for this Product --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                {{ __('Sales Order History') }}
                <span class="badge bg-light text-secondary border ms-2" style="font-size: 0.7rem;">{{ $sales->count() }} {{ __('orders') }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered table-nowrap align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 3%;" class="border-0 rounded-start text-center ps-3">#</th>
                            <th class="border-0 text-start" style="width: 40%;">{{ __('Order Line Items') }}</th>
                            <th class="border-0 text-center">{{ __('Sale Date') }}</th>
                            <th class="border-0 text-start">{{ __('Customer') }}</th>
                            <th class="border-0 text-end">{{ __('Discount') }}</th>
                            <th class="border-0 text-end">{{ __('Tax / TVA') }}</th>
                            <th class="border-0 text-end">{{ __('Total') }}</th>
                            <th class="border-0 text-center rounded-end pe-3">{{ __('Sold By') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                        <tr wire:key="tr_key_{{ $sale->id }}">
                            <td class="text-center ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td class="py-2 text-start">
                                <div class="bg-light p-2 rounded border">
                                    <div class="mb-1 pb-1 border-bottom d-flex justify-content-between align-items-center">
                                        <a href="{{ route('sales.show', $sale->id) }}" class="fw-bold text-dark font-monospace text-decoration-none small">
                                            {{ $sale->receipt_no ?: sprintf('TP-%06d', $sale->id) }}
                                        </a>
                                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size: 0.65rem;">
                                            {{ __('View Sale') }}
                                        </a>
                                    </div>
                                    @foreach ($sale->saleItems as $item)
                                        <div class="d-flex justify-content-between align-items-center mb-1 pb-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                                            <div>
                                                <span class="fw-semibold text-dark">{{ $item?->product->name }}</span>
                                                @if ($item->productVariation?->variation_name)
                                                    <span class="badge bg-primary text-white ms-1" style="font-size: 0.68rem;">{{ $item->productVariation->variation_name }}</span>
                                                @endif
                                                <div class="text-muted font-monospace" style="font-size: 0.72rem;">
                                                    {{ $item->productVariation?->sku ?: $item?->product->sku }}
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-white text-dark border me-1">{{ $item->quantity }}x</span>
                                                <span class="fw-bold font-monospace text-dark">€{{ number_format((float) $item->total, 2) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-semibold text-dark d-block">{{ $sale->created_at?->format('Y-m-d') }}</span>
                                <small class="text-muted">{{ $sale->created_at?->diffForHumans() }}</small>
                            </td>                            
                            <td class="text-start">
                                <span class="fw-semibold text-dark d-block">{{ $sale->customer?->name ?? __('Walk-in Customer') }}</span>
                                @if ($sale->customer?->phone_no)
                                    <small class="text-muted font-monospace">{{ $sale->customer->phone_no }}</small>
                                @endif
                            </td>
                            <td class="text-end font-monospace text-danger">
                                @if ((float) $sale->discount > 0)
                                    -€{{ number_format((float) $sale->discount, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end font-monospace text-muted">
                                €{{ number_format((float) $sale->gst, 2) }}
                            </td>
                            <td class="text-end font-monospace fw-bold fs-6 text-dark">
                                €{{ number_format((float) $sale->total_price, 2) }}
                            </td>
                            <td class="text-center pe-3 text-muted small">
                                {{ $sale->createdBy?->name ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">{{ __('No sales recorded for this product yet.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>
