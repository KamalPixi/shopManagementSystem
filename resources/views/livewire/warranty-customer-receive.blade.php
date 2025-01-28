<div>
    @include('includes.breadcrumb', ['data' => ['Warranty Customer Receive' => '']])

    <div wire:loading.flex wire:target="create,delete,edit,update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

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
                            <th class="border-0 text-center lh-1"> <small>(From Repair Shop)</small> <br> Receive details</th>
                            <th class="border-0 text-center lh-1"> <small>(Customer)</small><br> Receive details</th>
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
                            <td class="px-1 text-center fw-bold align-items-center lh-1">
                                <small class="text-muted">{{ $warranty->receive_date }}</small> <br>
                                <small class="text-muted">{{ $warranty->receive_remark }}</small>
                            </td>
                            <td class="px-1 text-center fw-bold align-items-center lh-1">
                                <small class="text-muted">{{ $warranty->customer_receive_date }}</small> <br>
                                <small class="text-muted">{{ $warranty->customer_receive_remark }}</small>
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
            <h2 class="h6 modal-title">Customer Receive Form</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form wire:submit.prevent="customerReceived">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="mb-4">
                            <small class="fw-bold">Cost</small>
                            <input wire:model="repair_cost" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="mb-4">
                            <small class="fw-bold">Paid</small>
                            <input wire:model="repair_cost_paid" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="mb-4">
                            <small class="fw-bold">Due</small>
                            <input wire:model="repair_cost_due" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <div class="mb-4">
                            <small class="fw-bold">Customer. Rcv. Date</small>
                            <input wire:model="customer_receive_date" type="date" class="form-control form-control-sm input-date">
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="mb-4">
                            <small class="fw-bold">Customer receive remark</small>
                            <textarea wire:model="customer_receive_remark" class="form-control form-control-sm" rows="1"></textarea>
                        </div>
                    </div>
                </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" wire:click="customerReceived">Customer has Received</button> 
            <button type="button" class="btn btn-link text-gray-600 ms-auto" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
      </div>
    </div>


</div>
