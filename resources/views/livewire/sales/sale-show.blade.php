<div>
    @include('includes.breadcrumb', ['data' => ['Sales' => route('sales.index'), 'View' => '']])

    @include('includes.alert')

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
        $receiptNumber = $sale->receipt_no ?: sprintf('TP-%06d', $sale->id);
        $paymentMethod = $sale->payment_method ?: 'cash';
    @endphp

    {{-- Sale Header Card --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                        <h3 class="fw-bold mb-0 text-dark font-monospace">{{ $receiptNumber }}</h3>
                        @if ($paymentMethod === 'cash')
                            <span class="badge bg-success text-white text-uppercase" style="font-size: 0.72rem;">{{ __('Cash') }}</span>
                        @elseif ($paymentMethod === 'card')
                            <span class="badge bg-info text-white text-uppercase" style="font-size: 0.72rem;">{{ __('Card') }}</span>
                        @else
                            <span class="badge bg-warning text-dark text-uppercase" style="font-size: 0.72rem;">{{ str_replace('_', ' ', $paymentMethod) }}</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-3 text-muted small">
                        <span>{{ __('Date') }}: <strong class="text-dark">{{ $sale->created_at?->format('Y-m-d h:i A') }}</strong></span>
                        <span class="text-secondary">•</span>
                        <span>{{ __('Sold By') }}: <strong class="text-dark">{{ $sale->createdBy?->name ?? '-' }}</strong></span>
                        @if ($sale->customer)
                            <span class="text-secondary">•</span>
                            <span>{{ __('Customer') }}: <strong class="text-dark">{{ $sale->customer->name }}</strong></span>
                        @endif
                    </div>
                </div>

                <div class="d-flex align-items-center flex-wrap gap-2">
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-primary shadow-sm px-3">
                        {{ __('Edit Sale') }}
                    </a>
                    <button type="button" wire:click="printSaleReceiptPos" class="btn btn-sm btn-outline-dark shadow-sm px-3">
                        {{ __('Print POS') }}
                    </button>
                    <button type="button" wire:click="printSaleReceipt" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                        {{ __('PDF Receipt') }}
                    </button>
                    <button type="button" wire:click="printSaleReceiptA4" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                        {{ __('PDF A4') }}
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                        {{ __('Back to Sales') }}
                    </a>
                </div>
            </div>

            {{-- Summary Metric Highlights --}}
            <div class="row g-2 mt-2 pt-2 border-top">
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center h-100">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">{{ __('Grand Total') }}</span>
                        <div class="fs-5 fw-bold text-dark font-monospace">
                            {{ $currencySymbol }}{{ number_format((float) $sale->total_price, 2) }}
                        </div>
                        <small class="text-muted" style="font-size: 0.68rem;">{{ __('Total amount charged') }}</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center h-100">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">{{ __('Discount Given') }}</span>
                        <div class="fs-5 fw-bold text-danger font-monospace">
                            @if ((float) $sale->discount > 0)
                                -{{ $currencySymbol }}{{ number_format((float) $sale->discount, 2) }}
                            @else
                                {{ $currencySymbol }}0.00
                            @endif
                        </div>
                        <small class="text-muted" style="font-size: 0.68rem;">{{ __('Order level discount') }}</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center h-100">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">{{ $shop?->tax_label ?? 'VAT / TVA' }}</span>
                        <div class="fs-5 fw-bold text-secondary font-monospace">
                            {{ $currencySymbol }}{{ number_format((float) $sale->gst, 2) }}
                        </div>
                        <small class="text-muted" style="font-size: 0.68rem;">{{ __('Calculated tax') }}</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center h-100">
                        <span class="text-muted small d-block mb-1" style="font-size: 0.75rem;">{{ __('Total Items') }}</span>
                        <div class="fs-5 fw-bold text-primary font-monospace">
                            {{ $sale->saleItems->sum('quantity') }}
                        </div>
                        <small class="text-muted" style="font-size: 0.68rem;">{{ __(':count line item(s)', ['count' => $sale->saleItems->count()]) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sale Line Items Table --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                {{ __('Purchased Items') }}
                <span class="badge bg-primary text-white ms-2" style="font-size: 0.7rem;">{{ $sale->saleItems->count() }} {{ __('items') }}</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered table-nowrap align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 rounded-start text-center ps-3" style="width: 3%;">#</th>
                            <th class="border-0 text-start" style="width: 40%;">{{ __('Product / Item') }}</th>
                            <th class="border-0 text-center">{{ __('Warranty') }}</th>
                            <th class="border-0 text-center">{{ __('Qty') }}</th>
                            <th class="border-0 text-end">{{ __('Unit Price') }}</th>
                            <th class="border-0 text-end">{{ __('Discount') }}</th>
                            <th class="border-0 text-end rounded-end pe-3">{{ __('Line Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale->saleItems as $item)
                            <tr>
                                <td class="text-center ps-3 text-muted">{{ $loop->iteration }}</td>
                                <td class="text-start">
                                    <div>
                                        @if ($item->product_id)
                                            <a href="{{ route('stocks.show', $item->product_id) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $item->name }}
                                            </a>
                                        @else
                                            <span class="fw-bold text-dark">{{ $item->name }}</span>
                                            <span class="badge bg-info text-white ms-1" style="font-size: 0.65rem;">{{ __('Custom') }}</span>
                                        @endif

                                        @if ($item->is_vatable)
                                            <span class="badge bg-success text-white ms-1" style="font-size: 0.62rem;">VAT</span>
                                        @endif

                                        @if ($item->productVariation)
                                            <span class="badge bg-primary text-white ms-1" style="font-size: 0.65rem;">
                                                {{ $item->productVariation->variation_name }}
                                            </span>
                                        @endif

                                        <div class="d-flex flex-wrap gap-2 text-muted font-monospace mt-1" style="font-size: 0.72rem;">
                                            @if ($item->productVariation?->sku || $item->product?->sku)
                                                <span>SKU: {{ $item->productVariation?->sku ?: $item->product?->sku }}</span>
                                            @endif
                                            @if (($item->productVariation?->serial_no || $item->product?->serial_no) && $item->show_serial_on_receipt)
                                                <span>SN/IMEI: {{ $item->productVariation?->serial_no ?: $item->product?->serial_no }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if ($item->warranty?->name)
                                        <span class="fw-semibold text-dark d-block">{{ $item->warranty->name }}</span>
                                        @if ($item->warranty_description)
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $item->warranty_description }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border fw-bold px-2 py-1">{{ $item->quantity }}</span>
                                </td>
                                <td class="text-end font-monospace text-muted">{{ $currencySymbol }}{{ number_format((float) $item->price, 2) }}</td>
                                <td class="text-end font-monospace text-danger">
                                    @if ((float) $item->discount > 0)
                                        -{{ $currencySymbol }}{{ number_format((float) $item->discount, 2) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3 font-monospace fw-bold text-dark fs-6">{{ $currencySymbol }}{{ number_format((float) $item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('No items recorded.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sale Metadata & Customer Details --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Customer Information') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-muted py-2" style="width: 35%;">{{ __('Customer Name') }}</td>
                                <td class="fw-semibold py-2">{{ $sale->customer?->name ?? __('Walk-in Customer') }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">{{ __('Phone Number') }}</td>
                                <td class="font-monospace py-2">{{ $sale->customer?->phone_no ?? '-' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">{{ __('Email') }}</td>
                                <td class="py-2">{{ $sale->customer?->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">{{ __('Address') }}</td>
                                <td class="py-2">{{ $sale->customer?->address ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-white border-bottom py-2 px-3">
                    <h6 class="fw-bold mb-0 text-dark">{{ __('Payment & Register Information') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-muted py-2" style="width: 35%;">{{ __('Payment Method') }}</td>
                                <td class="fw-semibold py-2 text-uppercase">{{ str_replace('_', ' ', $paymentMethod) }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">{{ __('Payment Reference') }}</td>
                                <td class="font-monospace py-2">{{ $sale->payment_reference ?: '-' }}</td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">{{ __('Slip Attachment') }}</td>
                                <td class="py-2">
                                    @if ($sale->payment_attachment)
                                        <a href="{{ asset($sale->payment_attachment) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                            {{ __('View Attachment') }}
                                        </a>
                                    @else
                                        <span class="text-muted">{{ __('None') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted py-2">{{ __('Cash Register') }}</td>
                                <td class="py-2">
                                    @if ($sale->cashRegisterSession)
                                        <span class="fw-semibold">{{ __('Session #:id (:date)', ['id' => $sale->cashRegisterSession->id, 'date' => $sale->cashRegisterSession->opened_at->format('Y-m-d')]) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
