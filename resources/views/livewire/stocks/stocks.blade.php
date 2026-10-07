<div>
    @include('includes.breadcrumb', ['data' => ['Stock List' => route('stocks.index')]])

    <div wire:loading.flex>
        @include('includes.spinner')
    </div>

    @include('includes.alert')


    {{-- Filter --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 d-flex align-items-center text-dark">
                            <i class="fa-solid fa-magnifying-glass me-2 text-primary"></i>{{ __('Search Stock') }}
                        </h5>
                        <button type="button" wire:click="clearFilter" class="btn btn-outline-danger btn-sm px-3">
                            <i class="fa-solid fa-rotate-left me-1"></i>{{ __('Clear Filters') }}
                        </button>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1 text-muted" style="font-size: 0.8rem;">{{ __('Product Name') }}</label>
                            <input wire:model.live="name" type="text" class="form-control form-control-sm" placeholder="{{ __('Search name...') }}">
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1 text-muted" style="font-size: 0.8rem;">{{ __('SKU / Barcode') }}</label>
                            <input wire:model.live="sku" type="text" class="form-control form-control-sm" placeholder="{{ __('SKU or barcode...') }}">
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1 text-muted" style="font-size: 0.8rem;">{{ __('Model No') }}</label>
                            <input wire:model.live="model_no" type="text" class="form-control form-control-sm" placeholder="{{ __('Model number...') }}">
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1 text-muted" style="font-size: 0.8rem;">{{ __('Category') }}</label>
                            <select wire:model.live="category_id" class="form-select form-select-sm">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1 text-muted" style="font-size: 0.8rem;">{{ __('Status') }}</label>
                            <select wire:model.live="status_filter" class="form-select form-select-sm">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="1">{{ __('Active') }}</option>
                                <option value="0">{{ __('Inactive') }}</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1 text-muted" style="font-size: 0.8rem;">{{ __('Product Type') }}</label>
                            <select wire:model.live="has_variations_filter" class="form-select form-select-sm">
                                <option value="">{{ __('All Products') }}</option>
                                <option value="1">{{ __('With Variations') }}</option>
                                <option value="0">{{ __('Single Product') }}</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1 text-muted" style="font-size: 0.8rem;">{{ __('Date Search Type') }}</label>
                            <select wire:model.live="filter_type" class="form-select form-select-sm">
                                <option value="day">{{ __('Day') }}</option>
                                <option value="month">{{ __('Month') }}</option>
                                <option value="year">{{ __('Year') }}</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3">
                            <label class="form-label mb-1 text-muted" style="font-size: 0.8rem;">{{ __('Stock Added Date') }}</label>
                            <input wire:model.live="stock_add_date" type="date" class="form-control form-control-sm">
                        </div>

                        <div class="col-12 pt-1">
                            <div class="d-flex align-items-center">
                                <div class="form-check m-0">
                                    <input wire:model.live="low_stock_only" class="form-check-input" type="checkbox" id="low_stock_only" style="cursor: pointer;">
                                    <label class="form-check-label fw-bold text-danger ms-1" for="low_stock_only" style="cursor: pointer; font-size: 0.82rem;">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ __('Low stock only') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Product list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-boxes-stacked me-2 text-primary"></i>{{ __('Stock List') }}
                    <span class="badge bg-light text-secondary border ms-2" style="font-size: 0.75rem;">{{ $products->total() }} {{ __('items') }}</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('stocks.add') }}" class="btn btn-sm btn-primary shadow-sm">
                        <i class="fa-solid fa-plus me-1"></i>{{ __('Add New Product') }}
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle shadow-sm" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-h me-1"></i> {{ __('Export & Barcodes') }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="dropdownMenuButton">
                            <li><a href="#" wire:click="pdfStocks" class="dropdown-item"><i class="fa-solid fa-file-pdf text-danger pe-2"></i>{{ __('Export Stock (PDF)') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="#" wire:click="pdfBarcodeOneColumn" class="dropdown-item"><i class="fa-solid fa-barcode text-primary pe-2"></i>{{ __('Barcode PDF (1 Column)') }}</a></li>
                            <li><a href="#" wire:click="pdfBarcodeTwoColumn" class="dropdown-item"><i class="fa-solid fa-barcode text-primary pe-2"></i>{{ __('Barcode PDF (2 Columns)') }}</a></li>
                            <li><a href="#" wire:click="printBarcodeTwoColumn" class="dropdown-item"><i class="fa-solid fa-print text-secondary pe-2"></i>{{ __('Print Barcode (2 Columns)') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mb-4 table-responsive">
                <table class="table table-centered table-hover table-nowrap mb-0 rounded align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 3%;" class="border-0 rounded-start text-center">
                                <input type="checkbox" class="form-check-input" wire:click='selectedAll(@json($products->pluck("id")->mapWithKeys(fn($id) => [$id => true])))' title="{{ __('Select all') }}">
                            </th>
                            <th class="border-0 text-start" style="min-width: 240px;">{{ __('Product / SKU') }}</th>
                            <th class="border-0 text-center">{{ __('Category') }}</th>
                            <th class="border-0 text-center">{{ __('Brand') }}</th>
                            <th class="border-0 text-center">{{ __('SN / IMEI') }}</th>
                            <th class="border-0 text-center">{{ __('Warranty') }}</th>
                            <th class="border-0 text-end">{{ __('Price') }}</th>
                            <th class="border-0 text-center">{{ __('Stock') }}</th>
                            <th class="border-0 text-center">{{ __('Status') }}</th>
                            <th class="border-0 text-center">{{ __('Added By') }}</th>
                            <th style="width: 5%;" class="border-0 text-end rounded-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                        <tr wire:key="tr_key_{{ $product->id }}">
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" wire:model="productIds.{{$product->id}}">
                            </td>
                            <td class="py-2 text-start">
                                <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                    <a href="{{ route('stocks.show', $product->id) }}" class="fw-bold text-dark text-decoration-none hover-primary fs-6" title="{{ __('View product details') }}">
                                        {{ $product->name }}
                                    </a>
                                    @if ($product->is_vatable)
                                        <span class="badge bg-success text-white" style="font-size: 0.65rem;">VAT</span>
                                    @endif
                                    @if ($product->has_variations && $product->variations->count() > 0)
                                        <span class="badge bg-primary text-white" style="font-size: 0.68rem;">{{ $product->variations->count() }} {{ __('Variants') }}</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-center flex-wrap gap-2 text-muted" style="font-size: 0.75rem;">
                                    @if (!empty($product->sku))
                                        <span><i class="fa-solid fa-barcode text-gray-400 me-1"></i><span class="font-monospace text-dark">{{ $product->sku }}</span></span>
                                    @endif
                                    @if (!empty($product->model_no))
                                        <span><i class="fa-solid fa-tag text-gray-400 me-1"></i>{{ $product->model_no }}</span>
                                    @endif
                                </div>

                                {{-- Attributes list if non-variant --}}
                                @if (!$product->has_variations && !empty($product->attributes_and_values))
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach ($product->attributes_and_values as $att_value)
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">
                                                {{ ucfirst($att_value['name']) }}: {{ implode(', ', $att_value['values']) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-center text-muted">
                                {{ $product->category?->name ?? '-' }}
                            </td>
                            <td class="text-center text-muted">
                                {{ $product->brand?->name ?? '-' }}
                            </td>
                            <td class="text-center font-monospace" style="font-size: 0.8rem;">
                                {{ $product->serial_no ?: '-' }}
                            </td>
                            <td class="text-center text-muted" style="font-size: 0.82rem;">
                                {{ $product->warranty?->name ?? '-' }}
                            </td>
                            <td class="text-end">
                                @if ($product->has_variations && $product->variations->count() > 0)
                                    @php
                                        $variantsTotalPrice = $product->variations->sum('price');
                                    @endphp
                                    <div class="fw-bold text-dark font-monospace" title="{{ __('Sum of all variation prices') }}">
                                        €{{ number_format((float) $variantsTotalPrice, 2) }}
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">{{ __('total variants sum') }}</small>
                                @else
                                    <div class="fw-bold text-dark font-monospace">
                                        €{{ number_format((float) $product->price, 2) }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($product->is_low_stock)
                                    <span class="badge bg-danger text-white py-1 px-2" title="{{ __('Low Stock! Threshold: :thresh', ['thresh' => $product->effective_low_stock_threshold]) }}">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $product->quantity }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border py-1 px-2 fw-bold" style="color: #1f2937 !important;">{{ $product->quantity }}</span>
                                @endif
                                @if ($product->has_variations && $product->variations->count() > 0)
                                    <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">{{ $product->variations->count() }} {{ __('variants') }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($product->getRawOriginal('status') == 1 || $product->status === 'Active')
                                    <span class="badge bg-success text-white" style="font-size: 0.7rem;">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary text-white" style="font-size: 0.7rem;">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-center text-muted small">
                                {{ $product->createdBy?->name ?? '-' }}
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary p-1 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="{{ route('stocks.show', $product->id) }}"><i class="fa-solid fa-eye text-info pe-2"></i>{{ __('View Details') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ route('stocks.edit', $product->id) }}"><i class="fa-solid fa-pen-to-square text-primary pe-2"></i>{{ __('Edit Product') }}</a></li>
                                        <li><a class="dropdown-item" href="#" wire:click="printBarcode('{{ $product->id }}')"><i class="fa-solid fa-barcode text-secondary pe-2"></i>{{ __('Print Barcode') }}</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" wire:click="delete('{{$product->id}}')" wire:confirm="{{ __('Are you sure you want to delete this product?') }}"><i class="fa-solid fa-trash pe-2"></i>{{ __('Delete') }}</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fa-solid fa-box-open fa-3x mb-3 text-gray-300 d-block"></i>
                                    <span class="fw-bold fs-6 text-gray-700">{{ __('No products found') }}</span>
                                    <p class="small text-muted mb-0 mt-1">{{ __('Try adjusting your search filters or click "+ Add New Product" to create one.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $products->links() }}
            </div>
        </div>
    </div>

</div>
