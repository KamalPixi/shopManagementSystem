<div>
    @include('includes.breadcrumb', ['data' => ['Pos' => '']])

    <div wire:loading.flex wire:target="create,delete,edit,update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

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
                        <i class="fa-solid fa-tag me-2 text-primary"></i> {{ __('Add Custom Item') }}
                    </h5>
                    <button wire:click="closeCustomModal" type="button" class="btn-close btn-close-white" aria-label="{{ __('Cancel') }}"></button>
                </div>
                <form wire:submit.prevent="addCustomItem">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Item / Service Name') }} <span class="text-danger">*</span></label>
                            <input wire:model="custom_item_name" type="text" class="form-control" placeholder="{{ __('e.g. Service / Item Name') }}" required>
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

                        <div class="mb-2">
                            <label class="form-label fw-bold small mb-1">{{ __('Warranty Description / Terms') }} <small class="text-muted fw-normal">({{ __('Optional') }})</small></label>
                            <input wire:model="custom_warranty_description" type="text" class="form-control" placeholder="{{ __('e.g. 1 Month warranty on replacement parts') }}">
                            @error('custom_warranty_description') <span class="text-danger small">{{ $message }}</span> @enderror
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

    {{-- Items list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            @if (!empty($errorMessage))
                <div x-data="{ show: true }" x-init="setTimeout(() => { show = false; $wire.set('errorMessage', ''); }, 5000)" x-show="show" x-transition.duration.300ms class="alert alert-danger alert-dismissible fade show d-flex align-items-center justify-content-between mb-3" role="alert">
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
                            <th class="border-0 text-start">{{ __('Product / Item') }}</th>
                            <th class="border-0 text-center">{{ __('Attributes') }}</th>
                            <th class="border-0 text-center">{{ __('Warranty') }}</th>
                            <th class="border-0 text-center">{{ __('Sale Price') }} <small class="small">{{ __('Unit') }}</small></th>
                            <th class="border-0 text-center">{{ __('Discount') }} <small class="small">({{ __('Fixed') }})</small></th>
                            <th class="border-0 text-center">{{ __('Qty') }}</th>
                            <th class="border-0 text-center">{{ __('Total') }}</th>
                            <th class="border-0 text-end">-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sale_items as $itemKey => $item)
                        @php
                            $prod = !empty($item['product_id']) ? collect($products)->firstWhere('id', $item['product_id']) : null;
                        @endphp
                        <tr wire:key="tr_key_{{ $itemKey }}">
                            <td style="width: 3%;">{{ $loop->iteration }}</td>
                            <td class="fw-bold align-items-center lh-1"> 
                                {{ $item['item_name'] ?? ($prod?->name ?? 'Item') }}
                                @if (!empty($item['is_custom']))
                                    <span class="badge bg-info text-white ms-1" style="font-size: 0.7rem;">{{ __('Custom / Service') }}</span>
                                @endif
                                @if ($prod?->serial_no)
                                    <br>
                                    <small class="text-muted">SN:{{ $prod->serial_no }}</small>
                                @endif
                                @if ($prod?->sku || $prod?->model_no)
                                    <br>
                                    <small class="text-muted">SKU: {{ $prod->sku }} | Model: {{ $prod->model_no }}</small>
                                @endif
                            </td>
                            <td class="fw-bold lh-1 text-center" style="width: 11%;">
                                @if ($prod && $prod->attributes_and_values)
                                <ul class="list-unstyled mb-0">
                                    @foreach ($prod->attributes_and_values as $att_and_values)
                                        <li class="d-flex align-items-center justify-content-between">
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
                                @else
                                    -
                                @endif
                            </td>
                            <td class="fw-bold align-items-center" style="width: 14%;">
                                <select wire:model.live="sale_items.{{$itemKey}}.warranty_id" class="form-select form-select-sm mb-1">
                                    <option value="">- {{ __('No Warranty') }} -</option>
                                    @foreach ($warranties as $warranty)
                                        <option value="{{ $warranty->id }}">{{ $warranty->name }}</option>
                                    @endforeach
                                </select>
                                <input wire:model.live="sale_items.{{$itemKey}}.warranty_description" type="text" class="form-control form-control-sm text-muted" placeholder="{{ __('Warranty terms / note') }}" style="font-size: 0.72rem; padding: 2px 5px;" title="{{ __('Edit warranty description') }}">
                            </td>
                            <td class="fw-bold align-items-center" style="width: 11%;">
                                <input wire:model.live="sale_items.{{$itemKey}}.price" type="number" step="0.01" class="form-control form-control-sm text-end">
                            </td>
                            <td class="fw-bold align-items-center" style="width: 11%;">
                                <input wire:model.live="sale_items.{{$itemKey}}.discount" type="number" step="0.01" class="form-control form-control-sm text-end">
                            </td>
                            <td class="fw-bold align-items-center" style="width: 8%;">
                                <div class="d-flex align-items-center">
                                    @if ($prod)
                                        <small class="text-muted badge badge-secondary" style="font-size: 0.8rem; padding-right: 2px;">{{ $prod->quantity }}:</small>
                                        <input wire:model.live="sale_items.{{$itemKey}}.quantity" type="number" min="1" max="{{ $prod->quantity }}" class="form-control form-control-sm text-center"> 
                                    @else
                                        <input wire:model.live="sale_items.{{$itemKey}}.quantity" type="number" min="1" class="form-control form-control-sm text-center"> 
                                    @endif
                                </div>
                            </td>
                            <td class="fw-bold align-items-center" style="width: 12%;">
                                <input wire:model="sale_items.{{$itemKey}}.total" type="number" readonly class="form-control form-control-sm text-end">
                            </td>

                            <td class="text-center px-0" style="width: 3%;">
                                <a class="dropdown-item text-danger text-center rounded" wire:click="delete('{{ $itemKey }}')" wire:confirm="{{ __('Are you sure you want to remove this item?') }}" href="#"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">{{ __('Scan a barcode or click + Custom Item to add items to the cart.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <table class="w-100">
                    <tr>
                        <td class="text-end fw-bold vertical-align-center align-middle py-0" style="width: 80%;">{{ __('Subtotal') }}:</td>
                        <td class="py-1"><input wire:model="subtotal" readonly type="number" class="fw-bold text-end form-control form-control-sm"></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-end fw-bold vertical-align-center align-middle" style="width: 80%;">{{ __('Discount') }}:</td>
                        <td class="py-1"><input wire:model.live="discount" readonly type="number" class="fw-bold text-end form-control form-control-sm"></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-end fw-bold vertical-align-center align-middle" style="width: 80%;">{{ $shop?->tax_label ?? 'GST' }} <span class="small text-muted">({{ $shop?->gst_percent }}%)</span>:</td>
                        <td class="py-1">
                            <input wire:model.live="gst" readonly type="number" class="fw-bold text-end form-control form-control-sm">
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1 text-end fw-bold vertical-align-center align-middle" style="width: 80%;">{{ __('Grand Total') }}:</td>
                        <td class="py-1"><input wire:model.live="grand_total" readonly type="number" class="fw-bold text-end form-control form-control-sm"></td>
                    </tr>
                </table>
            </div>

            <div class="row rounded pt-2 mb-4" style="background-color: rgb(228, 228, 228); margin-left:0.01rem;margin-right:0.01rem;">
                <div class="col">
                    <div class="mb-4 position-relative">
                        <label>{{ __('Customer name') }} <small class="text-muted fw-normal">({{ __('Optional') }})</small></label>
                        <input wire:model.live="customer_name" type="text" class="form-control form-control-sm" placeholder="{{ __('Walk In') }}">

                        @if (collect($customers)->count())
                        <div wire:key="customer_div" class="position-absolute rounded w-100 shadow mt-1 border overflow-scroll" style="background-color: rgb(243, 243, 243); max-height: 10rem;">
                            <ul class="list-unstyled m-0 py-2 w-100">
                                @foreach ($customers as $customer)
                                    <li wire:key="customer_li_{{$customer->id}}" class="px-3 w-100"><a wire:click="setCustomer('{{ $customer->id }}')" href="#" class="text-info"> {{ $customer->name }} {{ $customer->phone_no }} </a></li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col">
                    <div class="mb-4">
                        <label>{{ __('Phone No.') }} <small class="text-muted fw-normal">({{ __('Optional') }})</small></label>
                        <input wire:model="customer_phone_no" type="text" class="form-control form-control-sm" placeholder="{{ __('Optional') }}">
                    </div>
                </div>
            </div>

            @if (count($sale_items) > 0)
            <div class="text-end">
                <button wire:click="sale" type="button" class="btn btn-primary px-6">{{ __('Sale') }}</button>
                <button wire:click="clearPos" type="button" class="btn btn-danger px-3">{{ __('Clear') }}</button>
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
