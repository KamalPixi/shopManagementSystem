<div>
    @include('includes.breadcrumb', ['data' => ['Users' => '']])

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
                            <div class="col-3">
                                <div class="mb-2">
                                    <label>{{ __('Name') }}*</label>
                                    <input wire:model="name" type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-2">
                                    <label>{{ __('Username') }}* <small class="text-muted" style="font-size: 0.7rem;">({{ __('For login') }})</small></label>
                                    <input wire:model="username" type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-2">
                                    <label>{{ __('Password') }}</label>
                                    <input wire:model="password" type="text" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="mb-2">
                                    <label>{{ __('User Type') }}*</label>
                                    <select wire:model="user_type" class="form-select">
                                        <option value="staff">{{ __('Staff') }}</option>
                                        <option value="admin">{{ __('Admin') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm"> @if($editId) {{ __('Update User') }} @else {{ __('Add User') }} @endif</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <form wire:submit.prevent="updateStaffPermissions">
                        <div class="row">
                            <div class="col">
                                <div class="mb-2">
                                    <label>{{ __('Staff Permissions') }}</label>
                                    <select wire:model="staff_permissions" class="form-select" multiple style="height: 20rem;">
                                        @foreach ($permissions as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Update Staff Permissions') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Brand list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <h1 class="h5 mb-4">{{ __('User List') }}</h1>
            <div class="mb-4 ">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 8%;" class="border-0 rounded-start">#</th>
                            <th class="border-0">{{ __('Name') }}</th>
                            <th class="border-0">{{ __('Username') }}</th>
                            <th class="border-0">{{ __('User Type') }}</th>
                            <th style="width: 8%;" class="border-0 text-end rounded-end">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                        <tr wire:key="tr_key_{{ $user->id }}">
                            <td>{{ $users->firstItem() + $loop->index }}</td>
                            <td class="fw-bold align-items-center"> {{ $user->name }} </td>
                            <td class="fw-bold align-items-center"> {{ $user->username }} </td>
                            <td class="fw-bold align-items-center"> {{ $user->user_type == 'admin' ? __('Admin') : __('Staff') }} </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                      <li><a class="dropdown-item" wire:click="edit('{{ $user->id }}')" href="#"><i class="fas fa-edit pe-2"></i> {{ __('Edit') }}</a></li>
                                      <li><a class="dropdown-item text-danger" wire:click="delete('{{ $user->id }}')" wire:confirm="{{ __('Are you sure you want to delete?') }}" href="#"><i class="fas fa-trash pe-2"></i> {{ __('Delete') }}</a></li>
                                    </ul>
                                  </div>
                                  
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>

</div>
