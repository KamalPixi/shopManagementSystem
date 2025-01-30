<div>
    @include('includes.breadcrumb', ['data' => ['Stock List' => route('stocks')]])

    <div wire:loading.flex>
        @include('includes.spinner')
    </div>

    @include('includes.alert')


    {{-- Filter --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <h1 class="h5 mb-4">Search Stock</h1>

                    <div class="row">
                        <div class="col">
                            <div class="mb-4">
                                <label for="description">Product name</label>
                                <input wire:model.live="name" type="text" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label for="description">SKU/Bercode</label>
                                <input wire:model.live="sku" type="text" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label for="description">Model No</label>
                                <input wire:model.live="model_no" type="text" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label for="brand">Category</label>
                                <select wire:model.live="category_id" class="form-select form-select-sm">
                                    <option value="" selected="selected" disabled>Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label>Stock Added Date</label>
                                <input wire:model.live="stock_add_date" type="date" class="form-control form-control-sm input-date">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-4">
                                <label for="brand">Date Search Type</label>
                                <select wire:model.live="filter_type" class="form-select form-select-sm">
                                    <option value="day">Day</option>
                                    <option value="month">Month</option>
                                    <option value="year">Year</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" wire:click="clearFilter" class="btn btn-danger btn-sm m-0 px-4" type="button">Clear</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Product list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h1 class="h5 mb-4">Stock List</h1>
                <div>
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a href="#" wire:click="printStocks" class="dropdown-item" type="button"><i class="fa-solid fa-print pe-2"></i>Stocks <small>(Print)</small> </a></li>
                            <li><a href="#" wire:click="printBarcodes" class="dropdown-item" type="button"><i class="fa-solid fa-print pe-2"></i>Barcodes <small>(Print)</small> </a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <div class="mb-4">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:1%;" class="border-0 rounded-start">#</th>
                            <th class="border-0 lh-1" style="width: 45%">Name <br> <small class="text-muted">SKU</small> </th>
                            <th class="border-0 text-center">Attributes</th>
                            <th class="border-0 text-center">Category</th>
                            <th class="border-0 text-center">Brand</th>
                            <th class="border-0 text-center">ModelNo</th>
                            <th class="border-0 text-center">SN/IMEI</th>
                            <th class="border-0 text-center">Warranty</th>
                            <th class="border-0 text-center">Price</th>
                            <th class="border-0 text-center">Stock</th>
                            <th class="border-0 text-center">Status</th>
                            <th style="width: 8%;" class="border-0 text-end rounded-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                        <tr wire:key="tr_key_{{ $product->id }}">
                            <td>{{ $products->firstItem() + $loop->index }}</td>
                            <td class="px-0 fw-bold align-items-center lh-1"> {{ $product->name }} <br> <span class="small text-muted">{{$product->sku}}</span></td>
                            <td class="px-0 fw-bold align-items-center text-center lh-1">
                                <table class="table table-striped w-100">
                                    @foreach ($product->attributes_and_values ?? [] as $att_value)
                                    <tr>
                                        <td class="text-start" style="padding:0.2rem;"> <small class="text-muted">{{ ucfirst($att_value['name']) }}:</small> </td>
                                        <td class="text-start" style="padding:0.2rem;"> <small> {{ implode(', ', $att_value['values']) }}</small></td>
                                    </tr>
                                    @endforeach

                                </table>
                            </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->category?->name }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->brand?->name }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->model_no }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->serial_no }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->warranty?->name }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->price }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->quantity }} </td>
                            <td class="px-0 fw-bold align-items-center text-center"> {{ $product->status }} </td>
                            <td class="px-0 text-end">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item" href="#" wire:click="printBarcode('{{ $product->id }}')"><i class="fa-solid fa-print pe-2"></i>Barcode  <small>(Print)</small></a></li>
                                        <li><a class="dropdown-item" href="{{ route('stocks.show', $product->id) }}"><i class="fa-solid fa-eye pe-2"></i>View</a></li>
                                        <li><a class="dropdown-item" href="{{ route('stocks.edit', $product->id) }}"><i class="fas fa-edit pe-2"></i> Edit</a></li>
                                        <li><a class="dropdown-item text-danger" wire:click="delete('{{ $product->id }}')" wire:confirm="Are you sure you want to delete?" href="#"><i class="fas fa-trash pe-2"></i> Delete</a></li>
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
