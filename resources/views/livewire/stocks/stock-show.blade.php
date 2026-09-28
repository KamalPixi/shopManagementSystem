<div>
    @include('includes.breadcrumb', ['data' => ['Stock' => route('stocks.index'), 'View' => '']])

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">


                <div class="card-body">
                    <h2 class="h5 mb-4">Product Details</h2>
                    <table class="table table-bordered table-striped mb-4">
                        <tbody>
                            <tr>
                                <th style="width:25%; padding:0.2rem 0.5rem;">Name</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Attributes</th>
                                <td style="padding:0.2rem 0.5rem;">
                                    @if (collect($product->attributes_and_values)->filter(fn($p) => !empty($p['name']))->count())
                                    <table style="margin:0; padding:0;">
                                        @foreach ($product->attributes_and_values ?? [] as $att_value)
                                        <tr>
                                            <td style="padding:0 1px;"> <small class="text-muted">{{ ucfirst($att_value['name']) }}:</small> </td>
                                            <td style="padding:0 1px;"> <small> {{ implode(', ', $att_value['values']) }}</small></td>
                                        </tr>
                                        @endforeach
                                    </table>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Description</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->description }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Category</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->category?->name }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Brand</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->brand?->name }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Warranty</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->warranty?->name }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Model No</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->model_no ? $product->model_no : '-' }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Serial No</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->serial_no ? $product->serial_no : '-' }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">SKU</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->sku }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Price</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->price }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Purchase Price</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->purchase_price }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Quantity</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->quantity }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Remark</th>
                                <td style="padding:0.2rem 0.5rem;">{{ $product->remark }}</td>
                            </tr>
                            <tr>
                                <th style="width:25%;padding:0.2rem 0.5rem;">Status</th>
                                <td style="padding:0.2rem 0.5rem;"><span class="{{ $product->status == 'Active' ? 'text-success' : 'text-danger' }}">{{ $product->status }}</span></td>
                            </tr>
                        </tbody>
                    </table>

                    @if ($product->has_variations)
                        <div class="card border-info-subtle mb-4">
                            <div class="card-body">
                                <h2 class="h5 mb-3"><i class="fa-solid fa-boxes-stacked text-info me-2"></i>Variation Inventory</h2>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0">
                                        <thead class="table-light"><tr><th>Variation</th><th>SKU</th><th>Serial / IMEI</th><th class="text-end">Sale Price</th><th class="text-end">Cost Price</th><th class="text-center">Stock</th><th class="text-center">Low Alert</th></tr></thead>
                                        <tbody>
                                            @forelse ($product->variations as $variation)
                                                <tr>
                                                    <td class="fw-semibold">{{ $variation->variation_name }}</td>
                                                    <td class="font-monospace">{{ $variation->sku ?: '-' }}</td>
                                                    <td class="font-monospace">{{ $variation->serial_no ?: '-' }}</td>
                                                    <td class="text-end">{{ number_format($variation->price, 2) }}</td>
                                                    <td class="text-end">{{ number_format($variation->purchase_price, 2) }}</td>
                                                    <td class="text-center"><span class="badge {{ $variation->quantity <= ($variation->low_stock_threshold ?? $product->effective_low_stock_threshold) ? 'bg-danger' : 'bg-light text-dark border' }}">{{ $variation->quantity }}</span></td>
                                                    <td class="text-center">{{ $variation->low_stock_threshold ?? $product->effective_low_stock_threshold }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center text-muted">No variations configured.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="">
                        <h2 class="h5 mb-2">Stock Add History</h2>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <th>#</th>
                                <th class="text-center">Purchase Date</th>
                                <th class="text-center">Attributes & Values</th>
                                <th class="text-center">Purchase Price</th>
                                <th class="text-center">Sale Price</th>
                                <th class="text-center">Purchase Quantity</th>
                                <th class="text-center">Supplier</th>
                                <th class="text-center">AddedBy</th>
                            </thead>
                            <tbody>
                                @foreach ($product->productPurchases as $p)
                                <tr>
                                    <td> {{ $loop->index + 1 }} </td>
                                    <td class="text-center">{{ $p->purchase_date?->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center">
                                            @if (collect($product->attributes_and_values)->filter(fn($p) => !empty($p['name']))->count())
                                            <table style="margin:0; padding:0;">
                                                @foreach ($product->attributes_and_values ?? [] as $att_value)
                                                <tr>
                                                    <td style="padding:0 1px;"> <small class="text-muted">{{ ucfirst($att_value['name']) }}:</small> </td>
                                                    <td style="padding:0 1px;"> <small> {{ implode(', ', $att_value['values']) }}</small></td>
                                                </tr>
                                                @endforeach
                                            </table>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $p->purchase_price }}</td>
                                    <td class="text-center">{{ $p->sale_price }}</td>
                                    <td class="text-center">{{ $p->quantity }}</td>
                                    <td class="text-center">{{ $p->supplier?->name }}</td>
                                    <td class="text-center">{{ $p->createdBy?->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
                
            </div>
        </div>
    </div>


    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <h1 class="h5 mb-4">Sale List</h1>

            <div class="mb-4 ">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:2%;" class="border-0 rounded-start text-start px-2">#</th>
                            <th class="border-0">Products</th>
                            <th class="border-0 text-center">Date</th>
                            <th class="border-0 text-center">Customer</th>
                            <th class="border-0 text-center">Discount</th>
                            <th class="border-0 text-center">GST</th>
                            <th class="border-0 text-center">Total</th>
                            <th class="border-0 text-center">SoldBy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                        <tr wire:key="tr_key_{{ $sale->id }}">
                            <td class="px-2 py-0 align-middle">{{ $loop->index + 1 }}</td>
                            <td class="fw-bold align-items-center lh-1 align-middle px-1" style="width: 60%;"> 
                                <table class="table table-striped table-bordered table-hover w-100">
                                    @foreach ($sale->saleItems as $item)
                                        <tr wire:key="sale_items_tr_{{$item->id}}">
                                            <td class="px-1 py-1" style="width: 45%;"> 
                                                <small>{{$item?->product->name }}</small>
                                                @if ($item->productVariation?->variation_name)<br><small class="text-primary">{{ $item->productVariation->variation_name }}</small>@endif <br>
                                                <small class="text-muted">{{ $item->productVariation?->sku ?: $item?->product->sku }} </small>
                                             </td>
                                            <td class="px-1 py-1 align-middle lh-1" style="width: 15%;"> 
                                                <table class="w-100">
                                                    @foreach ($item->attributes_and_values ?? [] as $att => $value)
                                                    @php($attributeName = is_array($value) ? ($value['name'] ?? '') : $att)
                                                    @php($attributeValue = is_array($value) ? ($value['value'] ?? '') : $value)
                                                    <tr>
                                                        <td class="text-muted px-0"> <small>{{ ucfirst($attributeName) }}</small></td>
                                                        <td class="px-0"><small><b>: {{ ucfirst($attributeValue) }}</b></small></td>
                                                    </tr>
                                                    @endforeach
                                                </table>
                                            </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Warranty: <br> <b class="text-primary">{{$item?->warranty?->name ?? '-' }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Qty: <br> <b class="text-primary">{{$item?->quantity }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Price: <br> <b class="text-primary">{{$item?->price }}</b></span>  </td>
                                            <td class="px-1 py-1 align-middle text-center"> <small class="text-muted lh-1">Subtotal: <br> <b class="text-primary">{{$item?->total }}</b></span>  </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td class="fw-bold align-items-center align-middle text-center lh-1"> 
                                <small>{{ $sale->created_at?->format('Y-m-d') }}</small> <br>
                                <small class="text-muted">{{ $sale->created_at?->diffForHumans() }}</small>
                            </td>                            
                            <td class="fw-bold align-items-center text-center lh-1 align-middle"> 
                                <small>{{ $sale->customer?->name }}</small>  <br>
                                 <small>{{ $sale->customer?->phone_no }}</small>
                            </td>
                            <td class="fw-bold align-items-center align-middle text-center"> {{ $sale->discount }} </td>
                            <td class="fw-bold align-items-center align-middle text-center"> {{ $sale->gst }} </td>
                            <td class="fw-bold align-items-center align-middle text-center fw-bold"> {{ $sale->total_price }} </td>
                            <td class="fw-bold align-items-center align-middle text-center fw-bold"> {{ $sale->createdBy?->name }} </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>
