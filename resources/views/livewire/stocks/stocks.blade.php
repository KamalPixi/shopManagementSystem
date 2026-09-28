<div>
    @include('includes.breadcrumb', ['data' => ['Stock List' => route('stocks.index')]])

    <div wire:loading.flex>
        @include('includes.spinner')
    </div>

    @include('includes.alert')


    {{-- Filter --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <h1 class="h5 mb-4">{{ __('Search Stock') }}</h1>

                    <div class="row">
                        <div class="col">
                            <div class="mb-4">
                                <label for="description">{{ __('Product name') }}</label>
                                <input wire:model.live="name" type="text" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label>{{ __('Status') }}</label>
                                <select wire:model.live="status_filter" class="form-select form-select-sm">
                                    <option value="">{{ __('All statuses') }}</option>
                                    <option value="1">{{ __('Active') }}</option>
                                    <option value="0">{{ __('Inactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label>{{ __('Product type') }}</label>
                                <select wire:model.live="has_variations_filter" class="form-select form-select-sm">
                                    <option value="">{{ __('All products') }}</option>
                                    <option value="1">{{ __('With variations') }}</option>
                                    <option value="0">{{ __('Single product') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col d-flex align-items-center pt-2">
                            <div class="form-check">
                                <input wire:model.live="low_stock_only" class="form-check-input" type="checkbox" id="low_stock_only">
                                <label class="form-check-label" for="low_stock_only">{{ __('Low stock only') }}</label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label for="description">{{ __('SKU/Bercode') }}</label>
                                <input wire:model.live="sku" type="text" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label for="description">{{ __('Model No') }}</label>
                                <input wire:model.live="model_no" type="text" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label for="brand">{{ __('Category') }}</label>
                                <select wire:model.live="category_id" class="form-select form-select-sm">
                                    <option value="" selected="selected" disabled>{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label>{{ __('Stock Added Date') }}</label>
                                <input wire:model.live="stock_add_date" type="date" class="form-control form-control-sm input-date" placeholder="{{ __('Choose date') }}">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label for="brand">{{ __('Date Search Type') }}</label>
                                <select wire:model.live="filter_type" class="form-select form-select-sm">
                                    <option value="day">{{ __('Day') }}</option>
                                    <option value="month">{{ __('Month') }}</option>
                                    <option value="year">{{ __('Year') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" wire:click="clearFilter" class="btn btn-danger btn-sm m-0 px-4">{{ __('Clear') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Product list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h1 class="h5 mb-4">{{ __('Stock List') }}</h1>
                <div>
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a href="#" wire:click="pdfStocks" class="dropdown-item" type="button"><i class="fa-solid fa-file pe-2"></i>{{ __('Stocks') }} <small>(PDF)</small> </a></li>
                            <li><a href="#" wire:click="pdfBarcodeOneColumn" class="dropdown-item" type="button"><i class="fa-solid fa-file pe-2"></i>Barcode <small>(PDF 1 Column)</small> </a></li>
                            <li><a href="#" wire:click="pdfBarcodeTwoColumn" class="dropdown-item" type="button"><i class="fa-solid fa-file pe-2"></i>Barcode <small>(PDF 2 Columns)</small> </a></li>
                            <li><a href="#" wire:click="printBarcodeTwoColumn" class="dropdown-item" type="button"><i class="fa-solid fa-print pe-2"></i>Barcode <small>(Print 2 Columns)</small> </a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <div class="mb-4 table-responsive">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:1%;" class="border-0 rounded-start">
                                <input type="checkbox" wire:click='selectedAll(@json($products->pluck("id")->mapWithKeys(fn($id) => [$id => true])))'>
                                #
                            </th>
                            <th class="border-0 lh-1" style="width: 45%">{{ __('Name') }} <br> <small class="text-muted">SKU</small> </th>
                            <th class="border-0 text-center">{{ __('Attributes') }}</th>
                            <th class="border-0 text-center">{{ __('Category') }}</th>
                            <th class="border-0 text-center">{{ __('Brand') }}</th>
                            <th class="border-0 text-center">{{ __('ModelNo') }}</th>
                            <th class="border-0 text-center">{{ __('SN/IMEI') }}</th>
                            <th class="border-0 text-center">{{ __('Warranty') }}</th>
                            <th class="border-0 text-center">{{ __('Price') }}</th>
                            <th class="border-0 text-center">{{ __('Stock') }}</th>
                            <th class="border-0 text-center">{{ __('Status') }}</th>
                            <th class="border-0 text-center">{{ __('AddedBy') }}</th>
                            <th style="width: 8%;" class="border-0 text-end rounded-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                        <tr wire:key="tr_key_{{ $product->id }}">
                            <td>
                                <input type="checkbox" wire:model="productIds.{{$product->id}}">
                                {{ $products->firstItem() + $loop->index }}
                            </td>
                            <td class="px-0 fw-bold align-items-center lh-1"> 
                                {{ $product->name }} 
                                @if ($product->is_vatable)
                                    <span class="badge bg-success ms-1" style="font-size: 0.65rem;">VAT</span>
                                @else
                                    <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">No VAT</span>
                                @endif
                                @if ($product->has_variations && $product->variations->count() > 0)
                                    <span class="badge bg-info text-white ms-1" style="font-size: 0.65rem;">{{ $product->variations->count() }} Variations</span>
                                @endif
                                <br> <span class="small text-muted">{{$product->sku}}</span>
                            </td>
                            <td class="px-0 fw-bold align-items-center text-center lh-1">
                                    <div class="small text-start">
                                    @foreach ($product->attributes_and_values ?? [] as $att_value)
                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ ucfirst($att_value['name']) }}: {{ implode(', ', $att_value['values']) }}</span>
                                    @endforeach
                                    </div>
                            </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->category?->name }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->brand?->name }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->model_no }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->serial_no }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->warranty?->name }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->price }} </td>
                            <td class="px-0 fw-bold align-items-center text-center">
                                @if ($product->is_low_stock)
                                    <span class="badge bg-danger py-1 px-2" title="{{ __('Low Stock Alert Threshold: :thresh', ['thresh' => $product->effective_low_stock_threshold]) }}">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $product->quantity }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border py-1 px-2">{{ $product->quantity }}</span>
                                @endif
                                @if ($product->has_variations)
                                    <small class="d-block text-muted mt-1">{{ $product->variations->count() }} {{ __('variants') }}</small>
                                @endif
                            </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->status }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->createdBy?->name }} </td>
                            <td class="px-0 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="#" wire:click="printBarcode('{{ $product->id }}')"><i class="fa-solid fa-print pe-2"></i>{{ __('Barcode (1 Column Print)') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ route('stocks.show', $product->id) }}"><i class="fa-solid fa-eye pe-2"></i>{{ __('Show') }}</a></li>
                                        <li><a class="dropdown-item" href="{{ route('stocks.edit', $product->id) }}"><i class="fa-solid fa-pen-to-square pe-2"></i>{{ __('Edit') }}</a></li>
                                        <li><a class="dropdown-item text-danger" href="#" wire:click="delete('{{$product->id}}')" wire:confirm="{{ __('Are you sure you want to delete?') }}"><i class="fa-solid fa-trash pe-2"></i>{{ __('Delete') }}</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $products->links() }}
        </div>
    </div>

</div>
