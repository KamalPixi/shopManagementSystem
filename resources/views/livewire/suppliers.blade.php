<div>
    @include('includes.breadcrumb', ['data' => ['Suppliers' => '']])

    <div wire:loading.flex wire:target="create,delete,edit,update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    {{-- Form --}}
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <form wire:submit.prevent="@if($supplierIdEdit) update @else create @endif">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label>Name*</label>
                                <input wire:model="name" type="text" class="form-control form-control-sm">
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label>Email</label>
                                <input wire:model="email" type="email" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Phone</label>
                                <input wire:model="phone" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Contact Person</label>
                                <input wire:model="contact_person" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Address</label>
                                <input wire:model="address" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>City</label>
                                <input wire:model="city" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>State</label>
                                <input wire:model="state" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Postal Code</label>
                                <input wire:model="postal_code" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Country</label>
                                <input wire:model="country" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Website</label>
                                <input wire:model="website" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 mb-3">
                                <label>Bank Details</label>
                                <textarea wire:model="bank_details" class="form-control form-control-sm"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label>Notes</label>
                                <textarea wire:model="notes" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            @if($supplierIdEdit) Update @else Add @endif Supplier
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Supplier list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body table-responsive">
            <table class="table table-centered table-nowrap mb-0 rounded">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Contact Person</th>
                        <th>City</th>
                        <th>Country</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr wire:key="supplier_{{ $supplier->id }}">
                            <td>{{ $suppliers->firstItem() + $loop->index }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->contact_person }}</td>
                            <td>{{ $supplier->city }}</td>
                            <td>{{ $supplier->country }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" wire:click="edit('{{ $supplier->id }}')" href="#"><i class="fas fa-edit pe-2"></i> Edit</a></li>
                                        <li><a class="dropdown-item text-danger" wire:click="delete('{{ $supplier->id }}')" wire:confirm="Delete supplier?" href="#"><i class="fas fa-trash pe-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $suppliers->links() }}
        </div>
    </div>
</div>
