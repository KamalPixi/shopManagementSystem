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
                                <label>{{ __('Name*') }}</label>
                                <input wire:model="name" type="text" class="form-control form-control-sm">
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('Email') }}</label>
                                <input wire:model="email" type="email" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('Phone') }}</label>
                                <input wire:model="phone" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('Contact Person') }}</label>
                                <input wire:model="contact_person" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('Address') }}</label>
                                <input wire:model="address" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('City') }}</label>
                                <input wire:model="city" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('State') }}</label>
                                <input wire:model="state" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('Postal Code') }}</label>
                                <input wire:model="postal_code" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('Country') }}</label>
                                <input wire:model="country" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-6 mb-3">
                                <label>{{ __('Website') }}</label>
                                <input wire:model="website" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 mb-3">
                                <label>{{ __('Bank Details') }}</label>
                                <textarea wire:model="bank_details" class="form-control form-control-sm"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label>{{ __('Notes') }}</label>
                                <textarea wire:model="notes" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            @if($supplierIdEdit) {{ __('Update Supplier') }} @else {{ __('Add Supplier') }} @endif
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
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Contact Person') }}</th>
                        <th>{{ __('City') }}</th>
                        <th>{{ __('Country') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
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
                                        <li><a class="dropdown-item" wire:click="edit('{{ $supplier->id }}')" href="#"><i class="fas fa-edit pe-2"></i> {{ __('Edit') }}</a></li>
                                        <li><a class="dropdown-item text-danger" wire:click="delete('{{ $supplier->id }}')" wire:confirm="{{ __('Delete supplier?') }}" href="#"><i class="fas fa-trash pe-2"></i> {{ __('Delete') }}</a></li>
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
