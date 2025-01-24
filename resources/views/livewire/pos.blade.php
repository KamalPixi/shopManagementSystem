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
                    <form wire:submit.prevent="add">
                        <div class="row">
                            <div class="col">
                                <div class="mb-4">
                                    <label>Barcode / SKU / SerialNo*</label>
                                    <input wire:model="identifier" type="text" class="form-control form-control-sm" autofocus required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Brand list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="mb-4">
                <table class="table table-centered table-nowrap mb-2 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 rounded-start">#</th>
                            <th class="border-0 text-start">Product</th>
                            <th class="border-0 text-center">Attributes</th>
                            <th class="border-0 text-center">Warranty</th>
                            <th class="border-0 text-center">Price <small class="small">Price Per Unit</small></th>
                            <th class="border-0 text-center">Qty</th>
                            <th class="border-0 text-center">Total</th>
                            <th  class="border-0 text-end">-</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                        <tr wire:key="tr_key_{{ $product->id }}">
                            <td style="width: 3%;">{{ $loop->index + 1 }}</td>
                            <td class="fw-bold align-items-center lh-1"> {{ $product->name }} <br> <span class="small">{{ $product->sku }}</span> </td>
                            <td class="fw-bold lh-1 text-center" style="width: 11%;">
                                @if ($product->attributes_and_values)
                                <ul class="list-unstyled">
                                    @foreach ($product->attributes_and_values as $att_and_values)
                                        <li class="d-flex align-items-center justify-content-between">
                                            <small class="text-muted pe-1">{{ ucfirst($att_and_values['name']) }}: </small> 
                                            <select style="width:60%" wire:model="sale_items.{{$product->id}}.attributes_and_values.{{$att_and_values['name']}}" class="form-select form-select-sm">
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
                            <td class="fw-bold align-items-center" style="width: 9%;">
                                <select wire:model="sale_items.{{$product->id}}.warranty_id" class="form-select form-select-sm">
                                    <option value="">-</option>
                                    @foreach ($warranties as $warranty)
                                        <option value="{{ $warranty->id }}">{{ $warranty->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="fw-bold align-items-center" style="width: 11%;"> <input wire:model.live="sale_items.{{$product->id}}.price" type="number" class="form-control form-control-sm text-end"> </td>
                            <td class="fw-bold align-items-center" style="width: 6%;"> <input wire:model.live="sale_items.{{$product->id}}.quantity" type="number" class="form-control form-control-sm text-center"> </td>
                            <td class="fw-bold align-items-center" style="width: 12%;"> <input wire:model="sale_items.{{$product->id}}.total" type="number" readonly class="form-control form-control-sm text-end"> </td>

                            <td class="text-center px-0" style="width: 3%;">
                                <a class="dropdown-item text-danger text-center rounded" wire:click="delete('{{ $product->id }}')" wire:confirm="Are you sure you want to delete?" href="#"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <table class="w-100">
                    <tr>
                        <td class="text-end fw-bold vertical-align-center align-middle py-0" style="width: 80%;">Subtotal:</td>
                        <td class="py-1"><input wire:model="subtotal" readonly type="number" class="fw-bold text-end form-control form-control-sm"></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-end fw-bold vertical-align-center align-middle" style="width: 80%;">Discount:</td>
                        <td class="py-1"><input wire:model.live="discount" type="number" class="fw-bold text-end form-control form-control-sm"></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-end fw-bold vertical-align-center align-middle" style="width: 80%;">GST <span class="small text-muted">({{ $shop?->gst_percent }}%)</span>:</td>
                        <td class="py-1">
                            <input wire:model="gst" readonly type="number" class="fw-bold text-end form-control form-control-sm">
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1 text-end fw-bold vertical-align-center align-middle" style="width: 80%;">Grand Total:</td>
                        <td class="py-1"><input wire:model="grand_total" readonly type="number" class="fw-bold text-end form-control form-control-sm"></td>
                    </tr>
                </table>
            </div>

            <div class="row rounded pt-2 mb-4" style="background-color: rgb(228, 228, 228); margin-left:0.01rem;margin-right:0.01rem;">
                <div class="col">
                    <div class="mb-4 position-relative">
                        <label>Customer name*</label>
                        <input wire:model.live="customer_name" type="text" class="form-control form-control-sm" required>

                        @if (!empty($customers))
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
                        <label>Phone No.*</label>
                        <input wire:model="customer_phone_no" type="text" class="form-control form-control-sm" required>
                    </div>
                </div>
            </div>

            @if ($products)
            <div class="text-end">
                <button wire:click="sale" type="button" class="btn btn-primary px-6" type="button">Sale</button>
                <button wire:click="clearPos" type="button" class="btn btn-danger px-3" type="button">Clear</button>
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
