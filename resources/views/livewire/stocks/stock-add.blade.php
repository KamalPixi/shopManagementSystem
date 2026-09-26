<div>
    @include('includes.breadcrumb', ['data' => ['Stock' => route('stocks.index'), 'AddStock' => '']])

    <div wire:loading.flex wire:target="create">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">
                <form wire:submit.prevent="create" class="card-body">
                    <h1 class="h5 mb-4">{{ __('Add New Product') }}</h1>

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Product Name*') }}</label>
                                <input wire:model="name" required type="text" class="form-control form-control-sm" placeholder="{{ __('Product Name') }}">
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
                                <input wire:model="sku" type="text" class="form-control form-control-sm" placeholder="{{ __('Scan or enter SKU') }}" required>
                                @error('sku') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Model No.') }}</label>
                                <input wire:model="model_no" type="text" class="form-control form-control-sm" placeholder="{{ __('Model No.') }}">
                                @error('model_no') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Serial No / IMEI') }}</label>
                                <input wire:model="serial_no" type="text" class="form-control form-control-sm" placeholder="{{ __('Serial Number') }}">
                                @error('serial_no') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Pricing & Stock Settings --}}
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label>{{ __('Purchase / Cost Price*') }}</label>
                                <input wire:model="purchase_price" required type="number" step="0.01" min="0" class="form-control form-control-sm text-end" placeholder="0.00">
                                @error('purchase_price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label>{{ __('Sale Price*') }}</label>
                                <input wire:model="price" required type="number" step="0.01" min="0" class="form-control form-control-sm text-end" placeholder="0.00">
                                @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label>{{ __('Stock Quantity*') }}</label>
                                <input wire:model="quantity" required type="number" min="0" class="form-control form-control-sm text-center" {{ $has_variations ? 'readonly' : '' }}>
                                @if ($has_variations)
                                    <small class="text-muted d-block">{{ __('Auto-calculated from variations') }}</small>
                                @endif
                                @error('quantity') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label>{{ __('Low Stock Alert Threshold') }}</label>
                                <input wire:model="low_stock_threshold" type="number" min="0" class="form-control form-control-sm text-center" placeholder="{{ __('e.g. 5, 0 (Default: 10)') }}">
                                <small class="text-muted d-block">{{ __('Alert when stock <= threshold') }}</small>
                                @error('low_stock_threshold') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- VATable Setting --}}
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch p-2 bg-light rounded border">
                                <input wire:model="is_vatable" class="form-check-input ms-0 me-2" type="checkbox" id="is_vatable" value="1">
                                <label class="form-check-label fw-bold mb-0" for="is_vatable">
                                    <i class="fa-solid fa-receipt me-1 text-primary"></i> {{ __('VAT\'able Product') }}
                                </label>
                                <div class="text-muted small ms-4">{{ __('Mark if this item is subject to Tax/GST (can still be adjusted at POS sale time per item).') }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch p-2 bg-light rounded border">
                                <input wire:model.live="has_variations" class="form-check-input ms-0 me-2" type="checkbox" id="has_variations">
                                <label class="form-check-label fw-bold mb-0" for="has_variations">
                                    <i class="fa-solid fa-boxes-stacked me-1 text-info"></i> {{ __('Product Has Variations') }}
                                </label>
                                <div class="text-muted small ms-4">{{ __('Check if this product has variants with own serial numbers, price, or stock.') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Variations Section --}}
                    @if ($has_variations)
                    <div class="card border border-info mb-4 bg-light">
                        <div class="card-header bg-gray-800 text-white py-2 px-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-white small fw-bold">
                                <i class="fa-solid fa-boxes-stacked me-1 text-warning"></i> {{ __('Product Variations') }}
                            </h6>
                            <button type="button" wire:click="addVariationField" class="btn btn-xs btn-primary">
                                <i class="fa-solid fa-plus me-1"></i> {{ __('+ Add Variation') }}
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered bg-white mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 25%;">{{ __('Variation Name* (e.g. Red / 128GB)') }}</th>
                                            <th style="width: 15%;">{{ __('SKU / Barcode') }}</th>
                                            <th style="width: 15%;">{{ __('Serial No / IMEI') }}</th>
                                            <th style="width: 12%;" class="text-end">{{ __('Sale Price*') }}</th>
                                            <th style="width: 12%;" class="text-end">{{ __('Cost Price') }}</th>
                                            <th style="width: 10%;" class="text-center">{{ __('Stock*') }}</th>
                                            <th style="width: 8%;" class="text-center">{{ __('Low Alert') }}</th>
                                            <th style="width: 3%;" class="text-center">-</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($variations as $idx => $v)
                                        <tr wire:key="var_row_{{ $idx }}">
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.variation_name" type="text" class="form-control form-control-sm" placeholder="{{ __('e.g. 128GB Black') }}" required>
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.sku" type="text" class="form-control form-control-sm" placeholder="{{ __('Variation SKU') }}">
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.serial_no" type="text" class="form-control form-control-sm" placeholder="{{ __('Serial Number') }}">
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.price" type="number" step="0.01" min="0" class="form-control form-control-sm text-end" placeholder="0.00" required>
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.purchase_price" type="number" step="0.01" min="0" class="form-control form-control-sm text-end" placeholder="0.00">
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.quantity" type="number" min="0" class="form-control form-control-sm text-center" required>
                                            </td>
                                            <td>
                                                <input wire:model="variations.{{ $idx }}.low_stock_threshold" type="number" min="0" class="form-control form-control-sm text-center" placeholder="{{ __('10') }}">
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" wire:click="deleteVariationField({{ $idx }})" class="btn btn-xs btn-link text-danger p-0">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-2">{{ __('No variations added yet. Click "+ Add Variation" above.') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- General Attributes --}}
                    <div class="row rounded py-2 mb-4" style="background-color: rgb(240, 243, 246); margin-left:0.01rem;margin-right:0.01rem;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0 fw-bold">{{ __('Attributes (e.g. Color, Size):') }}</label>
                            <button type="button" wire:click="addAttributeField" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-plus me-1"></i> {{ __('Add Attribute') }}</button>
                        </div>
                        @foreach ($productAttributes as $k => $productAttribute)
                            <div wire:key="key_{{ $k }}" class="row px-0 mx-0 mb-2">
                                <div class="col-12 col-md-6">
                                    <input wire:model="productAttributes.{{$loop->index}}.name" type="text" class="form-control form-control-sm" placeholder="Ex: Color">
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex gap-2 align-items-center">
                                        <input wire:model="productAttributes.{{$loop->index}}.values" type="text" class="form-control form-control-sm" placeholder="Ex: Green,Blue,White">
                                        @if ($k != 0)
                                            <button type="button" wire:click="deleteAttributeField('{{ $k }}')" class="btn btn-xs btn-link text-danger p-0">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>            
                        @endforeach
                    </div>

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
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label>{{ __('Description') }}</label>
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
                            <i class="fa-solid fa-save me-1"></i> {{ __('Save Product') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>