<div>
    @include('includes.breadcrumb', ['data' => ['Pos' => '']])

    <div wire:loading.flex wire:target="sale,create,delete,edit,update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp

    {{-- Top Register Status Bar --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center p-2 px-3 rounded shadow-sm {{ $currentSession ? 'bg-white border-start border-4 border-success' : 'bg-white border-start border-4 border-warning' }}">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-cash-register me-2 {{ $currentSession ? 'text-success' : 'text-warning' }} fs-5"></i>
                    <div>
                        @if ($currentSession)
                            <span class="fw-bold text-dark">{{ __('Cash Register is OPEN') }}</span>
                            <span class="text-muted small ms-2">({{ __('Opened by: :user with :sym:amount', ['user' => $currentSession->user?->name ?? 'Staff', 'sym' => $currencySymbol, 'amount' => number_format($currentSession->opening_balance, 2)]) }})</span>
                        @else
                            <span class="fw-bold text-warning">{{ __('Cash Register is CLOSED') }}</span>
                            <span class="text-muted small ms-2">{{ __('No daily opening balance set yet.') }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <a href="{{ route('cashRegister') }}" class="btn btn-xs btn-outline-dark">
                        <i class="fa-solid fa-drawer me-1"></i> {{ __('Register & Drawer') }}
                    </a>
                    <a href="{{ route('moneyMovements') }}" class="btn btn-xs btn-outline-info ms-1">
                        <i class="fa-solid fa-money-bill-transfer me-1"></i> {{ __('Cash In / Out') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

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
                                    <input wire:model="custom_price" type="number" step="0.01" min="0" class="form-control text-end" placeholder="0.00" required>
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
                                <input wire:model="custom_purchase_price" type="number" step="0.01" min="0" class="form-control text-end" placeholder="0.00">
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
                                <input wire:model="custom_quantity" type="number" min="1" class="form-control text-center fw-bold" required>
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

    {{-- Items List Card --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            @if (!empty($errorMessage))
                <div x-data="{ show: true }" x-init="setTimeout(() => { show = false; $wire.set('errorMessage', ''); }, 5000)" x-show="show" class="alert alert-danger alert-dismissible fade show d-flex align-items-center justify-content-between mb-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <div>{{ $errorMessage }}</div>
                    </div>
                    <button type="button" wire:click="$set('errorMessage', '')" class="btn-close" aria-label="Close"></button>
                </div>
            @endif

            <div class="mb-4 table-responsive">
                <table class="table table-centered table-nowrap mb-2 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 rounded-start">#</th>
                            <th class="border-0 text-start" style="width: 28%;">{{ __('Product / Item') }}</th>
                            <th class="border-0 text-center" style="width: 8%;">{{ __('VAT?') }}</th>
                            <th class="border-0 text-center" style="width: 10%;">{{ __('Attributes') }}</th>
                            <th class="border-0 text-center" style="width: 16%;">{{ __('Warranty & Print Options') }}</th>
                            <th class="border-0 text-center" style="width: 10%;">{{ __('Sale Price') }}</th>
                            <th class="border-0 text-center" style="width: 9%;">{{ __('Discount') }}</th>
                            <th class="border-0 text-center" style="width: 7%;">{{ __('Qty') }}</th>
                            <th class="border-0 text-center" style="width: 9%;">{{ __('Total') }}</th>
                            <th class="border-0 text-end rounded-end" style="width: 3%;">-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale_items as $itemKey => $item)
                        @php
                            $prod = !empty($item['product_id']) ? collect($products)->firstWhere('id', $item['product_id']) : null;
                        @endphp
                        <tr wire:key="tr_key_{{ $itemKey }}">
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-bold align-items-center lh-1"> 
                                {{ $item['item_name'] ?? ($prod?->name ?? 'Item') }}
                                @if (!empty($item['is_custom']))
                                    <span class="badge bg-info text-white ms-1" style="font-size: 0.65rem;">{{ __('Custom') }}</span>
                                @endif

                                @if (!empty($item['serial_no']))
                                    <div class="mt-1 d-flex align-items-center">
                                        <small class="text-muted me-2 font-monospace">SN: {{ $item['serial_no'] }}</small>
                                        <label class="form-check-label text-muted" style="font-size: 0.7rem; cursor: pointer;">
                                            <input type="checkbox" wire:model.live="sale_items.{{ $itemKey }}.show_serial_on_receipt" class="form-check-input" style="width: 13px; height: 13px;">
                                            {{ __('Show SN on Receipt') }}
                                        </label>
                                    </div>
                                @endif

                                @if (!empty($item['sku']))
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">SKU: {{ $item['sku'] }}</small>
                                @endif
                            </td>

                            {{-- Freedom for VATable at POS time --}}
                            <td class="text-center align-middle">
                                <div class="form-check d-inline-block">
                                    <input type="checkbox" wire:model.live="sale_items.{{ $itemKey }}.is_vatable" class="form-check-input" id="vat_{{ $itemKey }}" style="width: 17px; height: 17px; cursor: pointer;">
                                </div>
                                <br>
                                @if (!empty($item['is_vatable']))
                                    <span class="badge bg-success" style="font-size: 0.65rem;">+VAT</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">No VAT</span>
                                @endif
                            </td>

                            {{-- Attributes --}}
                            <td class="fw-bold lh-1 text-center">
                                @if ($prod && $prod->attributes_and_values && empty($item['product_variation_id']))
                                <ul class="list-unstyled mb-0">
                                    @foreach ($prod->attributes_and_values as $att_and_values)
                                        <li class="d-flex align-items-center justify-content-between mb-1">
                                            <small class="text-muted pe-1">{{ ucfirst($att_and_values['name']) }}: </small> 
                                            <select style="width:60%" wire:model="sale_items.{{$itemKey}}.attributes_and_values.{{$att_and_values['name']}}" class="form-select form-select-sm">
                                                <option value="-" selected>-</option>
                                                @foreach ($att_and_values['values'] as $value)
                                                    <option wire:key="att_select_{{$value}}" value="{{$value}}">{{ucfirst($value)}}</option>
                                                @endforeach
                                            </select>
                                        </li>
                                    @endforeach
                                </ul>
                                @elseif(!empty($item['variation_name']))
                                    <span class="badge bg-light text-dark border">{{ $item['variation_name'] }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Warranty & Receipt Option --}}
                            <td>
                                <select wire:model.live="sale_items.{{$itemKey}}.warranty_id" class="form-select form-select-sm mb-1">
                                    <option value="">- {{ __('No Warranty') }} -</option>
                                    @foreach ($warranties as $warranty)
                                        <option value="{{ $warranty->id }}">{{ $warranty->name }}</option>
                                    @endforeach
                                </select>
                                <input wire:model.live="sale_items.{{$itemKey}}.warranty_description" type="text" class="form-control form-control-sm text-muted mb-1" placeholder="{{ __('Warranty terms') }}" style="font-size: 0.72rem; padding: 2px 5px;">
                                @if (!empty($item['warranty_id']) || !empty($item['warranty_description']))
                                    <label class="form-check-label text-muted" style="font-size: 0.7rem; cursor: pointer;">
                                        <input type="checkbox" wire:model.live="sale_items.{{ $itemKey }}.show_warranty_on_receipt" class="form-check-input" style="width: 13px; height: 13px;">
                                        {{ __('Print Warranty on Receipt') }}
                                    </label>
                                @endif
                            </td>

                            {{-- Sale Price --}}
                            <td>
                                <input wire:model.live="sale_items.{{$itemKey}}.price" type="number" step="0.01" class="form-control form-control-sm text-end">
                            </td>

                            {{-- Discount --}}
                            <td>
                                <input wire:model.live="sale_items.{{$itemKey}}.discount" type="number" step="0.01" class="form-control form-control-sm text-end">
                            </td>

                            {{-- Qty --}}
                            <td>
                                <input wire:model.live="sale_items.{{$itemKey}}.quantity" type="number" min="1" class="form-control form-control-sm text-center fw-bold">
                            </td>

                            {{-- Total --}}
                            <td>
                                <input wire:model="sale_items.{{$itemKey}}.total" type="number" readonly class="form-control form-control-sm text-end fw-bold">
                            </td>

                            <td class="text-center px-0">
                                <a class="dropdown-item text-danger text-center rounded p-1" wire:click="delete('{{ $itemKey }}')" wire:confirm="{{ __('Remove this item?') }}" href="#">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">{{ __('Scan a barcode/serial or click + Custom Item to add items to the cart.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Checkout Bottom Area: Payment, Customer, and Totals --}}
            <div class="row g-3">
                {{-- Payment Details Column --}}
                <div class="col-lg-6 col-12">
                    <div class="card border border-light-subtle bg-light p-3 rounded h-100">
                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="fa-solid fa-credit-card me-2 text-primary"></i> {{ __('Payment Information') }}
                        </h6>

                        <div class="mb-3">
                            <label class="small fw-bold mb-1">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" wire:model.live="payment_method" class="btn-check" name="btnradio" id="btnradio_cash" value="cash" autocomplete="off" checked>
                                <label class="btn btn-outline-success fw-bold" for="btnradio_cash">
                                    <i class="fa-solid fa-money-bill-wave me-1"></i> {{ __('Cash') }}
                                </label>

                                <input type="radio" wire:model.live="payment_method" class="btn-check" name="btnradio" id="btnradio_card" value="card" autocomplete="off">
                                <label class="btn btn-outline-info fw-bold" for="btnradio_card">
                                    <i class="fa-solid fa-credit-card me-1"></i> {{ __('Card') }}
                                </label>

                                <input type="radio" wire:model.live="payment_method" class="btn-check" name="btnradio" id="btnradio_bank" value="bank_transfer" autocomplete="off">
                                <label class="btn btn-outline-warning text-dark fw-bold" for="btnradio_bank">
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
                    <div class="card border border-light-subtle bg-light p-3 rounded h-100 d-flex flex-column justify-content-between">
                        <table class="w-100 mb-3">
                            <tr>
                                <td class="text-end fw-bold py-1" style="width: 70%;">{{ __('Subtotal') }}:</td>
                                <td class="py-1">
                                    <input wire:model="subtotal" readonly type="number" class="fw-bold text-end form-control form-control-sm bg-white">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold py-1">{{ __('Discount') }}:</td>
                                <td class="py-1">
                                    <input wire:model.live="discount" readonly type="number" class="fw-bold text-end form-control form-control-sm bg-white">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold py-1">
                                    {{ $shop?->tax_label ?? 'GST' }} <span class="small text-muted">({{ $shop?->gst_percent }}% on VAT items)</span>:
                                </td>
                                <td class="py-1">
                                    <input wire:model.live="gst" readonly type="number" class="fw-bold text-end form-control form-control-sm bg-white">
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td class="text-end fw-bold fs-5 py-2 text-dark">{{ __('Grand Total') }}:</td>
                                <td class="py-2">
                                    <input wire:model.live="grand_total" readonly type="number" class="fs-5 fw-bold text-end form-control form-control-sm bg-white text-primary border-primary">
                                </td>
                            </tr>
                        </table>

                        @if (count($sale_items) > 0)
                        <div class="d-flex gap-2">
                            <button wire:click="clearPos" type="button" class="btn btn-outline-danger w-30 py-2">
                                <i class="fa-solid fa-trash-can me-1"></i> {{ __('Clear') }}
                            </button>
                            <button wire:click="sale" type="button" class="btn btn-primary w-70 py-2 fw-bold shadow-sm fs-6">
                                <i class="fa-solid fa-check-circle me-1"></i> {{ __('Complete Sale (:sym:amt)', ['sym' => $currencySymbol, 'amt' => $grand_total]) }}
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
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
