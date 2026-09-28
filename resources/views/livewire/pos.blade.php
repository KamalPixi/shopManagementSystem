<div>
    @include('includes.breadcrumb', ['data' => ['Pos' => '']])

    <div wire:loading.flex wire:target="sale,create,delete,edit,update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp



    {{-- Scan & Custom Item Bar --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <div class="row align-items-end g-3">
                        <div class="col-lg-9 col-md-8 col-12">
                            <form wire:submit.prevent="add" class="m-0 p-0">
                                <div>
                                    <label class="form-label mb-1 fw-bold">{{ __('Barcode / SKU / SerialNo*') }}</label>
                                    <input wire:model="identifier" type="text" class="form-control m-0" style="height: 42px; line-height: 42px; box-sizing: border-box;" autofocus placeholder="{{ __('Scan barcode or enter SKU / Serial number') }}">
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-3 col-md-4 col-12">
                            <button wire:click="openCustomModal" type="button" class="btn btn-info text-white w-100 d-inline-flex align-items-center justify-content-center text-nowrap shadow-sm m-0" style="height: 42px; line-height: 1; box-sizing: border-box; white-space: nowrap;">
                                <i class="fa-solid fa-plus-circle me-2"></i> {{ __('+ Custom Item') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($pendingVariationProductId)
        @php
$pendingProduct = \App\Models\Product::with('variations')->find($pendingVariationProductId)
@endphp
        @if ($pendingProduct)
            <div class="card border-info shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div><strong>{{ $pendingProduct->name }}</strong><span class="text-muted ms-2">{{ __('Select a variation to add to cart') }}</span></div>
                        <button type="button" wire:click="cancelVariationSelection" class="btn btn-sm btn-outline-secondary">{{ __('Cancel') }}</button>
                    </div>
                    <div class="row g-2">
                        @forelse ($pendingProduct->variations as $variation)
                            <div class="col-12 col-md-6 col-xl-3">
                                <button type="button" wire:click="selectVariation({{ $variation->id }})" class="btn btn-outline-primary w-100 text-start h-100" @disabled($variation->quantity < 1)>
                                    <strong class="d-block">{{ $variation->variation_name }}</strong>
                                    <small class="d-block">{{ $variation->sku ?: $pendingProduct->sku }}</small>
                                    <small>{{ number_format($variation->price, 2) }} · {{ __('Stock') }}: {{ $variation->quantity }}</small>
                                </button>
                            </div>
                        @empty
                            <div class="col-12 text-muted">{{ __('No variations configured.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Custom Item Modal --}}
    @if ($showCustomModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 620px;">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-gray-800 text-white py-3 px-4 rounded-top-3">
                    <h5 class="modal-title h6 mb-0 text-white d-flex align-items-center">
                        <i class="fa-solid fa-tag me-2 text-primary"></i> {{ __('Add Custom Item / Service') }}
                    </h5>
                    <button wire:click="closeCustomModal" type="button" class="btn-close btn-close-white" aria-label="{{ __('Cancel') }}"></button>
                </div>
                <form wire:submit.prevent="addCustomItem">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Item / Service Name') }} <span class="text-danger">*</span></label>
                            <input wire:model="custom_item_name" type="text" class="form-control" placeholder="{{ __('e.g. Phone Repair Service / Special Cable') }}" required>
                            @error('custom_item_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold small mb-1">{{ __('Sale Price') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input wire:model="custom_price" type="number" step="0.01" min="0" class="form-control text-end" placeholder="0" required>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach ([10, 20, 30, 40, 50, 100] as $preset)
                                        <button type="button" wire:click="setCustomSalePrice({{ $preset }})" class="btn btn-xs btn-outline-primary rounded-1 py-0 px-2" style="font-size: 0.72rem;">
                                            {{ $preset }}
                                        </button>
                                    @endforeach
                                </div>
                                @error('custom_price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold small mb-1">{{ __('Cost / Purchase Price') }} <small class="text-muted fw-normal">({{ __('Optional') }})</small></label>
                                <input wire:model="custom_purchase_price" type="number" step="0.01" min="0" class="form-control text-end" placeholder="0">
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach ([0, 5, 10, 15, 20, 30] as $preset)
                                        <button type="button" wire:click="setCustomPurchasePrice({{ $preset }})" class="btn btn-xs btn-outline-secondary rounded-1 py-0 px-2" style="font-size: 0.72rem;">
                                            {{ $preset }}
                                        </button>
                                    @endforeach
                                </div>
                                @error('custom_purchase_price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold small mb-1">{{ __('Quantity') }} <span class="text-danger">*</span></label>
                                <input wire:model="custom_quantity" type="number" min="1" class="form-control text-center fw-bold" required placeholder="0">
                                @error('custom_quantity') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold small mb-1">{{ __('Warranty') }} <small class="text-muted fw-normal">({{ __('Optional') }})</small></label>
                                <select wire:model.live="custom_warranty_id" class="form-select">
                                    <option value="">{{ __('No Warranty') }}</option>
                                    @foreach ($warranties as $warranty)
                                        <option value="{{ $warranty->id }}">{{ $warranty->name }}</option>
                                    @endforeach
                                </select>
                                @error('custom_warranty_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Warranty Description / Terms') }} <small class="text-muted fw-normal">({{ __('Optional') }})</small></label>
                            <input wire:model="custom_warranty_description" type="text" class="form-control" placeholder="{{ __('e.g. 1 Month warranty on replacement parts') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-check form-switch">
                                    <input wire:model="custom_is_vatable" class="form-check-input" type="checkbox" id="custom_is_vatable">
                                    <label class="form-check-label fw-bold small" for="custom_is_vatable">{{ __('VAT\'able Item') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-check form-switch">
                                    <input wire:model="custom_show_warranty_on_receipt" class="form-check-input" type="checkbox" id="custom_show_warranty">
                                    <label class="form-check-label fw-bold small" for="custom_show_warranty">{{ __('Show Warranty on Receipt') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4 rounded-bottom-3 d-flex justify-content-between">
                        <button wire:click="closeCustomModal" type="button" class="btn btn-outline-secondary px-4">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fa-solid fa-plus me-1"></i> {{ __('Add to Cart') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Daily Cash Register Opening Balance Modal (Mandatory to Proceed) --}}
    @if ($showOpenRegisterModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-gray-800 text-white py-3 px-4 rounded-top-3">
                    <h5 class="modal-title h6 mb-0 text-white d-flex align-items-center">
                        <i class="fa-solid fa-cash-register me-2 text-warning"></i> {{ __('Enter Today\'s Opening Balance') }}
                    </h5>
                    <button wire:click="closeRegisterModal" type="button" class="btn-close btn-close-white" aria-label="{{ __('Close') }}"></button>
                </div>
                <form wire:submit.prevent="submitOpeningBalance">
                    <div class="modal-body p-4">
                        <div class="alert alert-warning d-flex align-items-center mb-3 py-2 px-3 small" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2 fs-6"></i>
                            <div>{{ __('You must enter today\'s opening cash balance before you can process sales.') }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Opening Cash in Drawer (:currency)', ['currency' => $currencySymbol]) }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">{{ $currencySymbol }}</span>
                                <input wire:model="pos_opening_balance" type="number" step="0.01" min="0" class="form-control form-control-lg text-end fw-bold" placeholder="0" autofocus required>
                            </div>
                            @error('pos_opening_balance') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold small mb-1">{{ __('Opening Notes / Remark') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                            <textarea wire:model="pos_opening_note" class="form-control" rows="2" placeholder="{{ __('e.g. Starting float in coins & small notes') }}"></textarea>
                            @error('pos_opening_note') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4 rounded-bottom-3 d-flex justify-content-between">
                        <button wire:click="closeRegisterModal" type="button" class="btn btn-outline-secondary px-4">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success text-white px-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-check me-1"></i> {{ __('Open Register & Continue') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Option B: Force Close Previous Day's Unclosed Register Modal --}}
    @if ($showClosePreviousDayModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.7); backdrop-filter: blur(3px);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-danger text-white py-3 px-4 rounded-top-3">
                    <h5 class="modal-title h6 mb-0 text-white d-flex align-items-center">
                        <i class="fa-solid fa-lock me-2"></i> {{ __('Unclosed Register From Previous Day') }}
                    </h5>
                    <button wire:click="closeClosePreviousDayModal" type="button" class="btn-close btn-close-white" aria-label="{{ __('Close') }}"></button>
                </div>
                <form wire:submit.prevent="submitClosePreviousDay">
                    <div class="modal-body p-4">
                        <div class="alert alert-danger d-flex align-items-center mb-3 py-2 px-3 small" role="alert">
                            <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                            <div>
                                <strong>{{ __('Action Required:') }}</strong>
                                {{ __('A cash register opened on :date (:time) was left unclosed. You must count and close that drawer before opening today\'s register or processing new sales.', [
                                    'date' => $overdueSession?->opened_at->format('d M Y') ?? 'Yesterday',
                                    'time' => $overdueSession?->opened_at->format('h:i A') ?? ''
                                ]) }}
                            </div>
                        </div>

                        <div class="alert alert-info py-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">{{ __('Expected Cash From Yesterday:') }}</span>
                                <span class="fs-5 fw-bold">{{ $currencySymbol }}{{ number_format($overdueExpected, 2) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Actual Counted Cash In Drawer') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">{{ $currencySymbol }}</span>
                                <input wire:model.live="pos_closing_balance" type="number" step="0.01" min="0" class="form-control form-control-lg text-end fw-bold" placeholder="0" autofocus required>
                            </div>
                            <div class="mt-2">
                                @if ($overdueDifference == 0)
                                    <span class="badge bg-success">{{ __('Balances perfectly (Diff: 0.00)') }}</span>
                                @elseif ($overdueDifference > 0)
                                    <span class="badge bg-info text-white">{{ __('Cash Over: +') }}{{ $currencySymbol }}{{ number_format($overdueDifference, 2) }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Cash Short: ') }}{{ $currencySymbol }}{{ number_format($overdueDifference, 2) }}</span>
                                @endif
                            </div>
                            @error('pos_closing_balance') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold small mb-1">{{ __('Closing Notes / Reason for Delayed Close') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                            <textarea wire:model="pos_closing_note" class="form-control" rows="2" placeholder="{{ __('Explain any discrepancy or reason why drawer was not closed last night...') }}"></textarea>
                            @error('pos_closing_note') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4 rounded-bottom-3 d-flex justify-content-between">
                        <button wire:click="closeClosePreviousDayModal" type="button" class="btn btn-outline-secondary px-4">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger text-white px-4 fw-bold shadow-sm">
                            <i class="fa-solid fa-lock me-1"></i> {{ __('Close Yesterday & Proceed To Today') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Items List Card --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">

            <div class="mb-4 table-responsive">
                <table class="table table-centered table-nowrap mb-2 rounded align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 rounded-start" style="width: 3%;">#</th>
                            <th class="border-0 text-start" style="width: 32%;">{{ __('Product / Item') }}</th>
                            <th class="border-0 text-start" style="width: 24%;">{{ __('Warranty & Tax') }}</th>
                            <th class="border-0 text-end" style="width: 15%;">{{ __('Price & Disc') }}</th>
                            <th class="border-0 text-center" style="width: 12%;">{{ __('Qty') }}</th>
                            <th class="border-0 text-end rounded-end" style="width: 14%;">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale_items as $itemKey => $item)
                        @php
                            $prod = !empty($item['product_id']) ? collect($products)->firstWhere('id', $item['product_id']) : null;
                            $availableStock = null;
                            if (!empty($item['product_variation_id'])) {
                                $availableStock = $item['stock'] ?? \App\Models\ProductVariation::where('id', $item['product_variation_id'])->value('quantity');
                            } elseif (!empty($item['product_id'])) {
                                $availableStock = $item['stock'] ?? ($prod?->quantity ?? \App\Models\Product::where('id', $item['product_id'])->value('quantity'));
                            }
                        @endphp
                        <tr wire:key="tr_key_{{ $itemKey }}">
                            <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                            <td class="py-2 text-start">
                                <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                    <span class="fw-bold text-dark fs-6">
                                        @if (!empty($item['variation_name']) && $prod?->name)
                                            {{ $prod->name }}
                                        @else
                                            {{ $item['item_name'] ?? ($prod?->name ?? 'Item') }}
                                        @endif
                                    </span>
                                    @if (!empty($item['is_custom']))
                                        <span class="badge bg-secondary text-white ms-1" style="font-size: 0.7rem; font-weight: 600;">{{ __('Custom') }}</span>
                                    @endif
                                    @if (!empty($item['variation_name']))
                                        <span class="badge bg-primary text-white ms-1" style="font-size: 0.72rem; font-weight: 600;">{{ $item['variation_name'] }}</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center flex-wrap gap-2 text-muted" style="font-size: 0.75rem;">
                                    @if (!empty($item['sku']))
                                        <span><i class="fa-solid fa-barcode text-gray-400 me-1"></i><span class="font-monospace">{{ $item['sku'] }}</span></span>
                                    @endif

                                    @if (!empty($item['serial_no']))
                                        <span class="d-inline-flex align-items-center font-monospace bg-gray-100 px-1 py-0 rounded border">
                                            SN: {{ $item['serial_no'] }}
                                        </span>
                                        <label class="form-check-label text-muted d-inline-flex align-items-center gap-1 ms-1" style="font-size: 0.7rem; cursor: pointer;">
                                            <input type="checkbox" wire:model.live="sale_items.{{ $itemKey }}.show_serial_on_receipt" class="form-check-input mt-0" style="width: 12px; height: 12px;">
                                            {{ __('On Receipt') }}
                                        </label>
                                    @endif
                                </div>

                                {{-- Legacy product attributes if non-variant --}}
                                @if ($prod && $prod->attributes_and_values && empty($item['product_variation_id']))
                                <div class="mt-2 p-2 bg-light rounded border">
                                    @foreach ($prod->attributes_and_values as $att_and_values)
                                        <div class="d-flex align-items-center justify-content-between mb-1 gap-2">
                                            <small class="text-muted">{{ ucfirst($att_and_values['name']) }}:</small>
                                            <select style="max-width: 140px;" wire:model="sale_items.{{$itemKey}}.attributes_and_values.{{$att_and_values['name']}}" class="form-select form-select-sm py-0">
                                                <option value="-" selected>-</option>
                                                @foreach ($att_and_values['values'] as $value)
                                                    <option wire:key="att_select_{{$value}}" value="{{$value}}">{{ucfirst($value)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                            </td>

                            {{-- Warranty & Tax --}}
                            <td class="py-2 text-start">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="flex-grow-1">
                                        <select wire:model.live="sale_items.{{$itemKey}}.warranty_id" class="form-select form-select-sm py-1" style="font-size: 0.78rem;">
                                            <option value="">{{ __('No Warranty') }}</option>
                                            @foreach ($warranties as $warranty)
                                                <option value="{{ $warranty->id }}">{{ $warranty->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <div class="form-check form-switch m-0" title="{{ __('Toggle VAT') }}">
                                            <input type="checkbox" wire:model.live="sale_items.{{ $itemKey }}.is_vatable" class="form-check-input" id="vat_{{ $itemKey }}" style="cursor: pointer;">
                                            <label class="form-check-label badge {{ !empty($item['is_vatable']) ? 'bg-success' : 'bg-secondary' }}" for="vat_{{ $itemKey }}" style="cursor: pointer; font-size: 0.65rem;">
                                                {{ !empty($item['is_vatable']) ? 'VAT' : 'NO VAT' }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <input wire:model.live="sale_items.{{$itemKey}}.warranty_description" type="text" class="form-control form-control-sm text-muted" placeholder="{{ __('Warranty terms/notes') }}" style="font-size: 0.73rem;">
                                    @if (!empty($item['warranty_id']) || !empty($item['warranty_description']))
                                        <label class="form-check-label text-muted d-inline-flex align-items-center gap-1 text-nowrap ms-1" style="font-size: 0.7rem; cursor: pointer;" title="{{ __('Print Warranty on Receipt') }}">
                                            <input type="checkbox" wire:model.live="sale_items.{{ $itemKey }}.show_warranty_on_receipt" class="form-check-input mt-0" style="width: 12px; height: 12px;">
                                            <span>{{ __('Print') }}</span>
                                        </label>
                                    @endif
                                </div>
                            </td>

                            {{-- Price & Discount --}}
                            <td class="py-2 text-end">
                                <div class="input-group input-group-sm mb-1" style="max-width: 130px; margin-left: auto;">
                                    <span class="input-group-text py-0 px-2 text-muted">€</span>
                                    <input wire:model.live="sale_items.{{$itemKey}}.price" type="number" step="0.01" class="form-control form-control-sm text-end fw-semibold py-1" placeholder="0.00" title="{{ __('Unit Price') }}">
                                </div>
                                <div class="input-group input-group-sm" style="max-width: 130px; margin-left: auto;">
                                    <span class="input-group-text py-0 px-2 text-muted" style="font-size: 0.7rem;">-€</span>
                                    <input wire:model.live="sale_items.{{$itemKey}}.discount" type="number" step="0.01" class="form-control form-control-sm text-end text-danger py-1" placeholder="0.00" title="{{ __('Line Discount') }}">
                                </div>
                            </td>

                            {{-- Qty Stepper & Stock Info --}}
                            <td class="py-2 text-center">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <div class="d-inline-flex align-items-center border rounded bg-white shadow-xs p-1">
                                        <button type="button" class="btn btn-sm btn-link text-secondary p-0 px-1" wire:click="decrementQuantity('{{ $itemKey }}')" title="{{ __('Decrease') }}">
                                            <i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i>
                                        </button>
                                        <input wire:model.live="sale_items.{{$itemKey}}.quantity" type="number" min="1" class="form-control form-control-sm text-center fw-bold border-0 p-0 shadow-none" style="width: 42px;" placeholder="1">
                                        <button type="button" class="btn btn-sm btn-link text-secondary p-0 px-1" wire:click="incrementQuantity('{{ $itemKey }}')" title="{{ __('Increase') }}">
                                            <i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </div>
                                    @if ($availableStock !== null)
                                        @php
                                            $remainingStock = $availableStock - (int)($item['quantity'] ?? 1);
                                        @endphp
                                        <div class="text-nowrap" title="{{ __('Available stock in inventory: :total', ['total' => $availableStock]) }}">
                                            <span class="badge {{ $remainingStock <= 2 ? 'bg-danger text-white' : 'bg-light text-secondary border' }}" style="font-size: 0.68rem; font-weight: 600; padding: 4px 6px;">
                                                {{ $remainingStock }} {{ __('left') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Total & Remove Action --}}
                            <td class="py-2 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <div class="text-end">
                                        <div class="fw-bold text-dark fs-6 font-monospace">€{{ number_format((float) ($item['total'] ?? 0), 2) }}</div>
                                        @if (!empty($item['discount']) && $item['discount'] > 0)
                                            <small class="text-success d-block" style="font-size: 0.7rem;">-€{{ number_format((float) $item['discount'], 2) }} {{ __('saved') }}</small>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1 ms-1" wire:click="delete('{{ $itemKey }}')" wire:confirm="{{ __('Remove this item?') }}" title="{{ __('Remove item') }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-cart-shopping fa-3x mb-3 text-gray-300 d-block"></i>
                                    <span class="fw-bold fs-6 text-gray-700">{{ __('Cart is empty') }}</span>
                                    <p class="small text-muted mb-0 mt-1">{{ __('Scan barcode, enter SKU / serial number, or click + Custom Item to add products.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Checkout Bottom Area: Shown ONLY when items are added to the cart --}}
            @if (count($sale_items) > 0)
            <div class="row g-3 mt-2 pt-3 border-top">
                {{-- Payment Details Column --}}
                <div class="col-lg-6 col-12">
                    <div class="card border border-light-subtle bg-light p-3 rounded h-100 shadow-none">
                        <h6 class="fw-bold mb-3 d-flex align-items-center text-dark">
                            <i class="fa-solid fa-credit-card me-2 text-primary"></i> {{ __('Payment Information') }}
                        </h6>

                        <div class="mb-3">
                            <label class="small fw-bold mb-1">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" wire:model.live="payment_method" class="btn-check" name="btnradio" id="btnradio_cash" value="cash" autocomplete="off">
                                <label class="btn {{ $payment_method === 'cash' ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }} fw-bold py-2" for="btnradio_cash">
                                    <i class="fa-solid fa-money-bill-wave me-1"></i> {{ __('Cash') }}
                                </label>

                                <input type="radio" wire:model.live="payment_method" class="btn-check" name="btnradio" id="btnradio_card" value="card" autocomplete="off">
                                <label class="btn {{ $payment_method === 'card' ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }} fw-bold py-2" for="btnradio_card">
                                    <i class="fa-solid fa-credit-card me-1"></i> {{ __('Card') }}
                                </label>

                                <input type="radio" wire:model.live="payment_method" class="btn-check" name="btnradio" id="btnradio_bank" value="bank_transfer" autocomplete="off">
                                <label class="btn {{ $payment_method === 'bank_transfer' ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }} fw-bold py-2" for="btnradio_bank">
                                    <i class="fa-solid fa-building-columns me-1"></i> {{ __('Bank Transfer') }}
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold mb-1">
                                {{ __('Payment Reference / Auth / Slip No.') }}
                                <small class="text-muted fw-normal">({{ __('Optional') }})</small>
                            </label>
                            <input wire:model="payment_reference" type="text" class="form-control form-control-sm" placeholder="{{ __('e.g. Card TXN-998877 or Bank Ref 12345678') }}">
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold mb-1">
                                {{ __('Payment Attachment (Slip / Receipt Photo)') }}
                                <small class="text-muted fw-normal">({{ __('Optional') }})</small>
                            </label>
                            <input wire:model="payment_attachment" type="file" accept="image/*,application/pdf" class="form-control form-control-sm">
                        </div>

                        {{-- Customer info --}}
                        <div class="row g-2 pt-2 border-top">
                            <div class="col-6">
                                <label class="small fw-bold mb-1">{{ __('Customer Name') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                <div class="position-relative">
                                    <input wire:model.live="customer_name" type="text" class="form-control form-control-sm" placeholder="{{ __('Walk In') }}">
                                    @if (collect($customers)->count())
                                    <div wire:key="customer_div" class="position-absolute rounded w-100 shadow mt-1 border overflow-scroll" style="background-color: rgb(243, 243, 243); max-height: 8rem; z-index: 1000;">
                                        <ul class="list-unstyled m-0 py-2 w-100">
                                            @foreach ($customers as $customer)
                                                <li wire:key="customer_li_{{$customer->id}}" class="px-3 w-100"><a wire:click="setCustomer('{{ $customer->id }}')" href="#" class="text-info"> {{ $customer->name }} {{ $customer->phone_no }} </a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold mb-1">{{ __('Phone Number') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                <input wire:model="customer_phone_no" type="text" class="form-control form-control-sm" placeholder="{{ __('Optional') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Totals Column --}}
                <div class="col-lg-6 col-12">
                    <div class="card border border-light-subtle bg-light p-3 rounded h-100 d-flex flex-column justify-content-between shadow-none">
                        <div>
                            <h6 class="fw-bold mb-3 d-flex align-items-center text-dark">
                                <i class="fa-solid fa-receipt me-2 text-primary"></i> {{ __('Order Summary') }}
                            </h6>
                            <table class="table table-sm table-borderless w-100 mb-3">
                                <tbody>
                                    <tr>
                                        <td class="text-start text-muted py-1">{{ __('Subtotal') }}</td>
                                        <td class="text-end fw-bold py-1">{{ $currencySymbol }}{{ number_format((float)$subtotal, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-start text-muted py-1">{{ __('Discount') }}</td>
                                        <td class="text-end fw-bold text-danger py-1">- {{ $currencySymbol }}{{ number_format((float)$discount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-start text-muted py-1">
                                            {{ $shop?->tax_label ?? 'TVA' }} <span class="small text-muted">({{ $shop?->gst_percent }}% on VAT items)</span>
                                        </td>
                                        <td class="text-end fw-bold py-1">{{ $currencySymbol }}{{ number_format((float)$gst, 2) }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="text-start fw-bold fs-5 py-2 text-dark">{{ __('Grand Total') }}</td>
                                        <td class="text-end fw-bold fs-4 py-2 text-primary">{{ $currencySymbol }}{{ number_format((float)$grand_total, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2 pt-2">
                            <button wire:click="clearPos" type="button" class="btn btn-outline-danger py-2 px-3 fw-bold">
                                <i class="fa-solid fa-trash-can me-1"></i> {{ __('Clear') }}
                            </button>
                            <button wire:click="sale" type="button" class="btn btn-primary flex-grow-1 py-2 fw-bold shadow-sm fs-6 d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-circle-check me-2 fs-5"></i> {{ __('Complete Sale (:sym:amt)', ['sym' => $currencySymbol, 'amt' => number_format((float)$grand_total, 2)]) }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <style>
        table tbody tr td {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        table thead tr th {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
    </style>
</div>
