<div>
    @include('includes.breadcrumb', ['data' => ['Customers' => '']])

    <div wire:loading.flex wire:target="create,delete,edit,update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    {{-- Form --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <form wire:submit.prevent="@if($customerIdEdit) update @else create @endif">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label>{{ __('Name*') }}</label>
                        <input wire:model="name" type="text" class="form-control form-control-sm">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-6 mb-3">
                        <label>{{ __('Email') }}</label>
                        <input wire:model="email" type="email" class="form-control form-control-sm">
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-6 mb-3">
                        <label>{{ __('Phone') }}</label>
                        <input wire:model="phone_no" type="text" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 mb-3">
                        <label>{{ __('Date of Birth') }}</label>
                        <input wire:model="dob" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 mb-3">
                        <label>{{ __('Address') }}</label>
                        <input wire:model="address" type="text" class="form-control form-control-sm">
                    </div>
                    <div class="col-4 mb-3">
                        <label>{{ __('City') }}</label>
                        <input wire:model="city" type="text" class="form-control form-control-sm">
                    </div>
                    <div class="col-4 mb-3">
                        <label>{{ __('State') }}</label>
                        <input wire:model="state" type="text" class="form-control form-control-sm">
                    </div>
                    <div class="col-4 mb-3">
                        <label>{{ __('Postal Code') }}</label>
                        <input wire:model="postal_code" type="text" class="form-control form-control-sm">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    @if($customerIdEdit) {{ __('Update Customer') }} @else {{ __('Add Customer') }} @endif
                </button>
            </form>
        </div>
    </div>

    {{-- Customer List --}}
    <div class="card border-0 shadow">
        <div class="card-body table-responsive">

            {{-- Search --}}
            <div class="mb-3">
                <input type="text" wire:model.live="search" class="form-control form-control-sm" placeholder="{{ __('Search customers by name, phone, or email...') }}">
            </div>

            <table class="table table-centered table-nowrap mb-0 rounded">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('City') }}</th>
                        <th>{{ __('State') }}</th>
                        <th>{{ __('DOB') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr wire:key="customer_{{ $customer->id }}">
                            <td>{{ $customers->firstItem() + $loop->index }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone_no }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->city }}</td>
                            <td>{{ $customer->state }}</td>
                            <td>{{ $customer->dob?->format('d-m-Y') }}</td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" wire:click="edit('{{ $customer->id }}')" href="#"><i class="fas fa-edit pe-2"></i> {{ __('Edit') }}</a></li>
                                        <li><a class="dropdown-item text-danger" wire:click="delete('{{ $customer->id }}')" wire:confirm="{{ __('Delete this customer?') }}" href="#"><i class="fas fa-trash pe-2"></i> {{ __('Delete') }}</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">{{ __('No customers found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
