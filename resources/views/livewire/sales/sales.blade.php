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
                        <div class="col-md-3 col-12">
                            <label class="small text-muted mb-1">{{ __('Receipt Number') }}</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fa-solid fa-receipt"></i></span>
                                <input wire:model.live.debounce.300ms="receipt_search" type="text" class="form-control form-control-sm font-monospace" placeholder="e.g. TP-000001 or 000001">
                            </div>
                        </div>

                        {{-- Filter by Staff --}}
                        <div class="col-md-3 col-6">
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
                                <option value="cash">{{ __('Cash') }}</option>
                                <option value="card">{{ __('Card') }}</option>
                                <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
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
                            <div class="col-md-2 col-6">
                                <label class="small text-muted mb-1">{{ __('From Date') }}</label>
                                <input wire:model.live="start_date" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2 col-6">
                                <label class="small text-muted mb-1">{{ __('To Date') }}</label>
                                <input wire:model.live="end_date" type="date" class="form-control form-control-sm">
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
                                            <td class="px-1 py-1 align-middle text-center" style="width: 15%;"> 
                                                <small class="text-muted lh-1">Warranty: <br> <b class="text-primary">{{$item?->warranty?->name ?? '-' }}</b></small>
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
                                    $pMethod = $sale->payment_method ?: 'cash';
                                    $pBadge = 'bg-success';
                                    if ($pMethod === 'card') $pBadge = 'bg-info text-white';
                                    elseif ($pMethod === 'bank_transfer') $pBadge = 'bg-warning text-dark';
                                @endphp
                                <span class="badge {{ $pBadge }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $pMethod) }}
                                </span>
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
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li><a class="dropdown-item text-dark" href="{{ route('sales.show', $sale->id) }}"><i class="fa-solid fa-eye pe-2"></i>{{ __('View Details') }}</a></li>
                                        <li><a class="dropdown-item text-primary" href="{{ route('sales.edit', $sale->id) }}"><i class="fa-solid fa-pen-to-square pe-2"></i>{{ __('Edit Sale') }}</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" wire:click="printSaleReceiptPos('{{$sale->id}}')"><i class="fa-solid fa-print pe-2"></i>{{ __('Receipt (POS)') }}</a></li>
                                        <li><a class="dropdown-item" href="#" wire:click="printSaleReceipt('{{$sale->id}}')"><i class="fa-solid fa-file-pdf pe-2 text-danger"></i>{{ __('Receipt (PDF)') }}</a></li>
                                        <li><a class="dropdown-item" href="#" wire:click="printSaleReceiptA4('{{$sale->id}}')"><i class="fa-solid fa-file-lines pe-2 text-info"></i>{{ __('Receipt A4 (PDF)') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ route('sales.a4Preview', $sale->id) }}" target="_blank"><i class="fa-solid fa-eye pe-2 text-info"></i>{{ __('A4 Preview') }}</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" wire:confirm="{{ __('Are you sure you want to delete this sale? Stock will be restored.') }}" wire:click="deleteSale('{{$sale->id}}')"><i class="fa-solid fa-trash pe-2"></i>{{ __('Delete') }}</a></li>
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
