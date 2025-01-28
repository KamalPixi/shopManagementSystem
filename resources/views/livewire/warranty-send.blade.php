<div>
    @include('includes.breadcrumb', ['data' => ['Warranty Send' => '']])

    <div wire:loading.flex wire:target="create,delete,edit,update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <form wire:submit.prevent="@if($editId) update @else create @endif">

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label>Reference No.</label>
                                    <input wire:model="reference_no" type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-4 position-relative">
                                    <label>Product*</label>
                                    <input wire:model.live="product_name" type="text" class="form-control form-control-sm" required>

                                    @if ($products->count())
                                    <div wire:key="product_div" class="position-absolute rounded w-100 shadow mt-1 border overflow-scroll" style="background-color: rgb(243, 243, 243); max-height: 10rem;">
                                        <ul class="list-unstyled m-0 py-2 w-100">
                                            @foreach ($products as $product)
                                                <li wire:key="product_li_{{$product->id}}" class="px-3 w-100"><a wire:click="setProductName('{{ $product->name }}')" href="#" class="text-info"> {{ $product->name }} </a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label for="description">Problem description</label>
                                    <textarea wire:model="problem_description" class="form-control form-control-sm" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label>Customer Name*</label>
                                    <input wire:model="customer_name" type="text" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label>Customer phone no.</label>
                                    <input wire:model="customer_phone_no" type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label for="description">Repair shop address/details</label>
                                    <textarea wire:model="repair_shop_details" id="description" class="form-control form-control-sm" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label>Repair cost </label>
                                    <input wire:model="repair_cost" type="number" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label>Repair cost paid.</label>
                                    <input wire:model="repair_cost_paid" type="number" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label for="description">Repair cost due</label>
                                    <input wire:model="repair_cost_due" type="number" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="mb-4">
                                    <label>Send date </label>
                                    <input wire:model="send_date" type="date" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="mb-4">
                                    <label>Remark</label>
                                    <textarea wire:model="remark" id="description" class="form-control" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm" type="button"> @if($editId) Update @else Send @endif Warranty</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Brand list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-4">
                        <label>Reference No</label>
                        <input wire:model.live="search_reference_no" type="text" class="form-control form-control-sm" placeholder="Search by reference no.">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-4">
                        <label>Customer name</label>
                        <input wire:model.live="search_customer_name" type="text" class="form-control form-control-sm" placeholder="Search by customer name.">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:2%;" class="border-0 rounded-start px-1">#</th>
                            <th class="border-0 px-1 lh-1">Reference No. <br> <small class="text-muted">Send Date</small></th>
                            <th class="border-0 px-1">Product</th>
                            <th class="border-0">Customer</th>
                            <th class="border-0">Problem</th>
                            <th class="border-0">Repair Shop</th>
                            <th class="border-0">Remark</th>
                            <th class="border-0 text-center">Cost</th>
                            <th class="border-0 text-center">Paid</th>
                            <th class="border-0 text-center">Due</th>
                            <th style="width: 8%;" class="border-0 text-end rounded-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($warranties as $warranty)
                        <tr wire:key="tr_key_{{ $warranty->id }}">
                            <td class="px-1">{{ $warranties->firstItem() + $loop->index }}</td>
                            <td style="width:10%;" class="px-1 align-items-center lh-1"> 
                                {{ $warranty->reference_no }} <br>
                                 <small class="text-muted">{{ $warranty->send_date }}</small> 
                             </td>
                            <td style="width: 15%" class="px-1 align-items-center"> {{ $warranty->product_name }} </td>
                            <td class="px-1 text-center align-items-center lh-1"> 
                                <small>{{ $warranty->customer_details['customer_name'] }}</small>  <br> 
                                <small>{{ $warranty->customer_details['customer_phone_no'] }}</small> 
                            </td>
                            <td class="px-1 text-center align-items-center" style="width: 20%;"> <textarea class="form-control form-control-sm" style="font-size: .7rem;" rows="1" readonly>{{ $warranty->problem_description }}</textarea> </td>
                            <td class="px-1 text-center align-items-center"> <textarea class="form-control form-control-sm" style="font-size: .7rem;" rows="1" readonly>{{ $warranty->repair_shop_details }}</textarea> </td>
                            <td class="px-1 text-center align-items-center" style="width: 20%;"> <textarea class="form-control form-control-sm" style="font-size: .7rem;" rows="1" readonly>{{ $warranty->remark }}</textarea> </td>
                            <td class="px-1 text-center fw-bold align-items-center"> {{ $warranty->repair_cost }} </td>
                            <td class="px-1 text-center fw-bold align-items-center"> {{ $warranty->repair_cost_paid }} </td>
                            <td class="px-1 text-center fw-bold align-items-center"> {{ $warranty->repair_cost_due }} </td>
                            <td class="px-1 text-center text-end">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                      <li><a class="dropdown-item" wire:click="markAsReceiveModal('{{ $warranty->id }}')" href="#"> <i class="fas fa-hand-holding pe-2"></i> MarkAsReceived</a></li>
                                      <li><a class="dropdown-item" wire:click="edit('{{ $warranty->id }}')" href="#"><i class="fas fa-edit pe-2"></i> Edit</a></li>
                                      <li><a class="dropdown-item text-danger" wire:click="delete('{{ $warranty->id }}')" wire:confirm="Are you sure you want to delete?" href="#"><i class="fas fa-trash pe-2"></i> Delete</a></li>
                                    </ul>
                                  </div>
                                  
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $warranties->links() }}
        </div>
    </div>




<div wire:ignore.self class="modal fade" id="modal-default" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h2 class="h6 modal-title">Receive From Repair Shop</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form wire:submit.prevent="customerReceived">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="mb-4">
                            <small class="fw-bold">Receive date</small>
                            <input wire:model="receive_date" type="date" class="form-control form-control-sm input-date">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="mb-4">
                            <small class="fw-bold">Receive remark</small>
                            <textarea wire:model="receive_remark" class="form-control form-control-sm" rows="1"></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" wire:click="markAsReceived">Received from repair shop</button> 
            <button type="button" class="btn btn-link text-gray-600 ms-auto" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
</div>
  
</div>
