<div>
    @include('includes.breadcrumb', ['data' => ['Stock' => route('stocks.index'), 'EditProduct' => '']])

    <div wire:loading.flex wire:target="update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">
                <form wire:submit.prevent="update" class="card-body">
                    <h1 class="h5 mb-4">{{ __('Update Product') }}</h1>

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Product Name*') }}</label>
                                <input wire:model="name" required type="text" class="form-control form-control-sm">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Category') }}</label>
                                <select wire:model="category_id" class="form-select">
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Brand') }}</label>
                                <select wire:model="brand_id" class="form-select">
                                    <option value="">{{ __('Select Brand') }}</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('SKU / Barcode*') }}</label>
                                <input wire:model="sku" type="text" class="form-control form-control-sm" required>
                                @error('sku') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Model No.') }}</label>
                                <input wire:model="model_no" type="text" class="form-control form-control-sm">
                                @error('model_no') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Serial No / IMEI') }}</label>
                                <input wire:model="serial_no" type="text" class="form-control form-control-sm">
                                @error('serial_no') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label>{{ $has_variations ? __('Purchase Price') : __('Purchase Price*') }} @if($has_variations)<small class="text-muted">({{ __('set per variation below') }})</small>@endif</label>
                                <input wire:model="purchase_price" type="number" step="0.01" min="0" class="form-control form-control-sm text-end" @disabled($has_variations) placeholder="0">
                                @error('purchase_price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label>{{ $has_variations ? __('Sale Price') : __('Sale Price*') }} @if($has_variations)<small class="text-muted">({{ __('set per variation below') }})</small>@endif</label>
                                <input wire:model="price" {{ $has_variations ? '' : 'required' }} type="number" step="0.01" min="0" class="form-control form-control-sm text-end" @disabled($has_variations) placeholder="0">
                                @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label>{{ $has_variations ? __('Quantity') : __('Quantity*') }}</label>
                                <input wire:model="quantity" {{ $has_variations ? '' : 'required' }} type="number" min="0" class="form-control form-control-sm text-center" {{ $has_variations ? 'readonly' : '' }} placeholder="0">
                                @if ($has_variations)
                                    <small class="text-muted d-block">{{ __('Calculated from variations') }}</small>
                                @endif
                                @error('quantity') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label>{{ __('Low Stock Alert Threshold') }} @if($has_variations)<small class="text-muted">({{ __('set per variation below') }})</small>@endif</label>
                                <input wire:model="low_stock_threshold" type="number" min="0" class="form-control form-control-sm text-center" placeholder="0" @disabled($has_variations)>
                                <small class="text-muted d-block">{{ $has_variations ? __('Use the threshold in each variation row.') : __('Alert when stock <= threshold') }}</small>
                                @error('low_stock_threshold') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- VATable Setting --}}
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <div class="form-check p-2 bg-light rounded border">
                                <input wire:model.live="is_vatable" class="form-check-input ms-0 me-2" type="checkbox" id="is_vatable_edit" value="1">
                                <label class="form-check-label fw-bold mb-0" for="is_vatable_edit">
                                    <i class="fa-solid fa-receipt me-1 text-primary"></i> {{ __('VAT\'able Product') }}
                                </label>
                                <div class="text-muted small ms-4">{{ __('Mark if this item is subject to Tax/GST (staff can still toggle at POS time).') }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check p-2 bg-light rounded border">
                                <input wire:model.live="has_variations" class="form-check-input ms-0 me-2" type="checkbox" id="has_variations_edit">
                                <label class="form-check-label fw-bold mb-0" for="has_variations_edit">
                                    <i class="fa-solid fa-boxes-stacked me-1 text-info"></i> {{ __('Product Has Variations') }}
                                </label>
                                <div class="text-muted small ms-4">{{ __('Manage variations with own serial numbers, price, or stock.') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Attributes Section --}}
                    <div class="card border border-light shadow-sm rounded-3 mb-4 bg-light">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <label class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center">
                                        <i class="fa-solid fa-layer-group text-primary me-2"></i> {{ __('Product Attributes (e.g. Color, Storage, Size)') }}
                                    </label>
                                    <small class="text-muted d-block">{{ __('Define attributes and comma-separated options. Example: Storage: 128GB, 256GB | Color: Black, Blue') }}</small>
                                </div>
                                <button type="button" wire:click="addAttributeField" class="btn btn-outline-primary btn-xs px-2 py-1">
                                    <i class="fa-solid fa-plus me-1"></i> {{ __('+ Add Another Attribute') }}
                                </button>
                            </div>

                            @foreach ($productAttributes as $k => $productAttribute)
                                <div wire:key="key_{{ $k }}" class="row g-2 align-items-center mb-2">
                                    <div class="col-12 col-md-4">
                                        <input wire:model="productAttributes.{{$loop->index}}.name" type="text" class="form-control form-control-sm" placeholder="{{ __('Attribute Name (e.g. Color or Storage)') }}">
                                    </div>
                                    <div class="col-12 col-md-7">
                                        <input wire:model="productAttributes.{{$loop->index}}.values" type="text" class="form-control form-control-sm" placeholder="{{ __('Comma-separated options: Black, Blue, Gold') }}">
                                    </div>
                                    <div class="col-12 col-md-1 text-center">
                                        @if ($k != 0)
                                            <button type="button" wire:click="deleteAttributeField('{{ $k }}')" class="btn btn-xs btn-outline-danger p-1" title="{{ __('Delete') }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>            
                            @endforeach

                            @if ($has_variations)
                                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span class="small text-muted">
                                        <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i>
                                        {{ __('Click generate to combine all attribute values into variations automatically.') }}
                                    </span>
                                    <button type="button" wire:click="generateVariationsFromAttributes" class="btn btn-sm btn-primary px-3 shadow-sm">
                                        <i class="fa-solid fa-bolt me-1"></i> {{ __('Generate Variations From Attributes') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Variations Section --}}
                    @if ($has_variations)
                    <div class="card border border-light shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-dark small fw-bold d-flex align-items-center">
                                <i class="fa-solid fa-boxes-stacked me-2 text-primary"></i> {{ __('Generated Variations & Inventory') }}
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2">{{ count($variations) }} {{ __('Variants') }}</span>
                            </h6>
                            <div class="d-flex gap-2">
                                <button type="button" wire:click="generateVariationsFromAttributes" class="btn btn-xs btn-outline-primary" title="{{ __('Regenerate from attributes') }}">
                                    <i class="fa-solid fa-rotate me-1"></i> {{ __('Sync from Attributes') }}
                                </button>
                                <button type="button" wire:click="addVariationField" class="btn btn-xs btn-outline-secondary">
                                    <i class="fa-solid fa-plus me-1"></i> {{ __('+ Add Manual Row') }}
                                </button>
                                @if (count($variations) > 0)
                                    <button type="button" wire:click="clearVariations" wire:confirm="{{ __('Clear all variations?') }}" class="btn btn-xs btn-outline-danger">
                                        <i class="fa-solid fa-trash me-1"></i> {{ __('Clear') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-3">
                            {{-- Alerts --}}
                            @if (session()->has('variation_success'))
                                <div class="alert alert-success-subtle border border-success-subtle py-2 px-3 mb-3 small text-success-emphasis rounded">
                                    <i class="fa-solid fa-circle-check me-1"></i> {{ session('variation_success') }}
                                </div>
                            @endif
                            @if (session()->has('variation_error'))
                                <div class="alert alert-warning-subtle border border-warning-subtle py-2 px-3 mb-3 small text-warning-emphasis rounded">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ session('variation_error') }}
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-sm table-centered table-bordered bg-white mb-0 align-middle">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 20%;">{{ __('Variation Name / Option') }} <span class="text-danger">*</span></th>
                                            <th style="width: 14%;">{{ __('Supplier') }}</th>
                                            <th style="width: 14%;">{{ __('SKU / Barcode') }}</th>
                                            <th style="width: 14%;">{{ __('Serial No / IMEI') }}</th>
                                            <th style="width: 11%;" class="text-end">{{ __('Sale Price') }} <span class="text-danger">*</span></th>
                                            <th style="width: 11%;" class="text-end">{{ __('Cost Price') }}</th>
                                            <th style="width: 7%;" class="text-center">{{ __('Stock') }} <span class="text-danger">*</span></th>
                                            <th style="width: 6%;" class="text-center">{{ __('Low Alert') }}</th>
                                            <th style="width: 3%;" class="text-center">-</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($variations as $idx => $v)
                                        <tr wire:key="var_edit_row_{{ $idx }}">
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.variation_name" type="text" class="form-control form-control-sm font-monospace fw-bold" placeholder="{{ __('e.g. 128GB / Black') }}" required>
                                            </td>
                                            <td>
                                                <select wire:model="variations.{{ $idx }}.supplier_id" class="form-select form-select-sm">
                                                    <option value="">{{ __('Default / None') }}</option>
                                                    @foreach ($suppliers as $sup)
                                                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.sku" type="text" class="form-control form-control-sm font-monospace" placeholder="{{ __('Auto or Barcode') }}">
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.serial_no" type="text" class="form-control form-control-sm font-monospace" placeholder="{{ __('Optional Serial/IMEI') }}">
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.price" type="number" step="0.01" min="0" class="form-control form-control-sm text-end fw-semibold" placeholder="0" required>
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.purchase_price" type="number" step="0.01" min="0" class="form-control form-control-sm text-end" placeholder="0" @disabled($has_variations)>
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.quantity" type="number" min="0" class="form-control form-control-sm text-center fw-bold" required placeholder="0">
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.low_stock_threshold" type="number" min="0" class="form-control form-control-sm text-center" placeholder="0">
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" wire:click="deleteVariationField({{ $idx }})" class="btn btn-xs btn-link text-danger p-0">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fa-solid fa-boxes-stacked fs-4 d-block mb-2 text-gray-400"></i>
                                                {{ __('No variations yet. Enter attributes above and click "Generate Variations From Attributes".') }}
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Purchase Date') }}</label>
                                <input wire:model="purchase_date" type="date" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Supplier') }}</label>
                                <select wire:model="supplier_id" class="form-select">
                                    <option value="">{{ __('Select Supplier') }}</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Warranty') }}</label>
                                <select wire:model="warranty_id" class="form-select">
                                    <option value="">{{ __('Select Warranty') }}</option>
                                    @foreach ($warranties as $warranty)
                                        <option value="{{ $warranty->id }}">{{ $warranty->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Payment Method') }}</label>
                                <select wire:model="payment_method_id" class="form-select">
                                    <option value="">{{ __('Select Payment Method') }}</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Product Description') }}</label>
                                <textarea wire:model="description" rows="2" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Remark') }}</label>
                                <textarea wire:model="remark" rows="2" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Status') }}</label>
                                <select wire:model="status" class="form-select">
                                    <option value="1">{{ __('Active') }}</option>
                                    <option value="0">{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-save me-1"></i> {{ __('Update Product') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
