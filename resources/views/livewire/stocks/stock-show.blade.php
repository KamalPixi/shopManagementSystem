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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    </div>




</div>
