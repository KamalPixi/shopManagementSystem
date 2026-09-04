<div>
    @include('includes.breadcrumb', ['data' => ['Sales' => route('sales.index')]])

    <div wire:loading.flex>
        @include('includes.spinner')
    </div>

    @include('includes.alert')


    {{-- Filter --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <h1 class="h5 mb-3">{{ __('Filter Sales') }}</h1>
                    <div class="row">
                        <div class="col">
                            <div class="">
                                <label for="description">{{ __('Sale Date') }}</label>
                                <input wire:model.live="sale_date" required type="date" class="form-control form-control-sm input-date">
                            </div>
                        </div>
                        <div class="col">
                            <div class="">
                                <label for="brand">{{ __('Filter By') }}</label>
                                <select wire:model.live="filter_by" class="form-select form-select-sm">
                                    <option value="DAY">{{ __('Day') }}</option>
                                    <option value="MONTH">{{ __('Month') }}</option>
                                    <option value="YEAR">{{ __('Year') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button wire:click="clearFilter" class="btn btn-sm btn-danger px-4">{{ __('Clear') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Product list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h1 class="h5 mb-4">{{ __('Sale List') }}</h1>
                <div>
                    <button type="button" wire:click="printSalesReport" class="btn btn-sm btn-secondary"><i class="fa-solid fa-print pe-2"></i>{{ __('Sales Report') }}</button>
                </div>
            </div>

            <div class="mb-4 table-responsive">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:2%;" class="border-0 rounded-start text-start px-2">#</th>
                            <th class="border-0">{{ __('Products') }}</th>
                            <th class="border-0 text-center">{{ __('T.Discount') }}</th>
                            <th class="border-0 text-center">{{ $shop?->tax_label ?? 'GST' }}</th>
                            <th class="border-0 text-center">{{ __('G.Total') }}</th>
                            <th class="border-0 text-center">{{ __('Date') }}</th>
                            <th class="border-0 text-center">{{ __('Customer') }}</th>
                            <th class="border-0 text-center">{{ __('SoldBy') }}</th>
                            <th style="width: 8%;" class="border-0 text-end rounded-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                        <tr wire:key="tr_key_{{ $sale->id }}">
                            <td class="px-2 py-0 align-middle">{{ $sales->firstItem() + $loop->index }}</td>
                            <td class="fw-bold align-items-center lh-1 align-middle px-1" style="width: 60%;"> 
                                <table class="table table-striped table-bordered table-hover w-100">
                                    @foreach ($sale->saleItems as $item)
                                        <tr wire:key="sale_items_tr_{{$item->id}}">
                                            <td class="px-1 py-1" style="width: 45%;"> 
                                                <small>{{ $item->name }}</small>
                                                @if (empty($item->product_id))
                                                    <span class="badge bg-info text-white" style="font-size: 0.6rem;">Custom</span>
                                                @endif
                                                @if ($item->product?->sku)
                                                    <br><small class="text-muted">{{ $item->product->sku }}</small>
                                                @endif
                                             </td>
                                            <td class="px-1 py-1 align-middle lh-1" style="width: 15%;"> 
                                                <table class="w-100">
                                                    @foreach ($item->attributes_and_values ?? [] as $att => $value)
                                                    <tr>
                                                        <td class="text-muted px-0"> <small>{{ ucfirst($att) }}</small></td>
                                                        <td class="px-0"><small><b>: {{ ucfirst($value) }}</b></small></td>
                                                    </tr>
                                                    @endforeach
                                                </table>
                                            </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Warranty: <br> <b class="text-primary">{{$item?->warranty?->name ?? '-' }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Qty: <br> <b class="text-primary">{{$item?->quantity }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">P.Price: <br> <b class="text-primary">{{$item?->purchase_price }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">S.Price: <br> <b class="text-primary">{{$item?->price }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Discount: <br> <b class="text-primary">{{$item?->discount }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Subtotal: <br> <b class="text-primary">{{$item?->total }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Profit: <br> <b class="text-primary">{{$item?->profit }}</b></span>  </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td class="fw-bold align-items-center align-middle text-center fw-bold"> {{ $sale->discount }} </td>
                            <td class="fw-bold align-items-center align-middle text-center fw-bold"> {{ $sale->gst }} </td>
                            <td class="fw-bold align-items-center align-middle text-center fw-bold"> {{ $sale->total_price }} </td>
                            <td class="fw-bold align-items-center align-middle text-center lh-1"> 
                                <small>{{ $sale->created_at?->format('Y-m-d') }}</small> <br>
                                <small class="text-muted">{{ $sale->created_at?->diffForHumans() }}</small>
                            </td>                            
                            <td class="fw-bold align-items-center text-center lh-1 align-middle"> 
                                <small>{{ $sale->customer?->name }}</small>  <br>
                                 <small>{{ $sale->customer?->phone_no }}</small>
                            </td>
                            <td class="fw-bold align-items-center align-middle text-center fw-bold"> {{ $sale->createdBy?->name }} </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li><a class="dropdown-item text-primary" href="{{ route('sales.edit', $sale->id) }}"><i class="fa-solid fa-pen-to-square pe-2"></i>{{ __('Edit Sale') }}</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" wire:click="printSaleReceiptPos('{{$sale->id}}')"><i class="fa-solid fa-print pe-2"></i>{{ __('Receipt (POS)') }}</a></li>
                                        <li><a class="dropdown-item" href="#" wire:click="printSaleReceipt('{{$sale->id}}')"><i class="fa-solid fa-print pe-2"></i>{{ __('Receipt (PDF)') }}</a></li>
                                        <li><a class="dropdown-item" href="#" wire:click="printSaleReceiptA4('{{$sale->id}}')"><i class="fa-solid fa-print pe-2"></i>{{ __('Receipt A4 (PDF)') }}</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" wire:confirm="{{ __('Are you sure you want to delete this item?') }}" wire:click="deleteSale('{{$sale->id}}')"><i class="fa-solid fa-trash pe-2"></i>{{ __('Delete') }}</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $sales->links() }}
        </div>
    </div>

</div>
