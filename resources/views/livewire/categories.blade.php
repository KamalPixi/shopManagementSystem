<div>
    @include('includes.breadcrumb', ['data' => ['Category' => '']])

    <div wire:loading.flex wire:target="create,delete,edit,update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">
                <div class="card-body">
                    <form wire:submit.prevent="@if($categoryIdEdit) update @else create @endif">

                        <div class="row">
                            <div class="col-6">
                                <div class="mb-4">
                                    <label for="category">Category Name*</label>
                                    <input wire:model="name" type="text" class="form-control form-control-sm" id="category">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-4">
                                    <label for="description">Description</label>
                                    <textarea wire:model="description" id="description" class="form-control form-control-sm" rows="1"></textarea>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary btn-sm" type="button"> @if($categoryIdEdit) Update @else Add @endif Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Brand list --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <div class="mb-4 ">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 8%;" class="border-0 rounded-start">#</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Description</th>
                            <th style="width: 8%;" class="border-0 text-end rounded-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                        <tr wire:key="tr_key_{{ $category->id }}">
                            <td>{{ $categories->firstItem() + $loop->index }}</td>
                            <td class="fw-bold align-items-center"> {{ $category->name }} </td>
                            <td class="fw-bold align-items-center"> {{ $category->description }} </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                      <li><a class="dropdown-item" wire:click="edit('{{ $category->id }}')" href="#"><i class="fas fa-edit pe-2"></i> Edit</a></li>
                                      <li><a class="dropdown-item text-danger" wire:click="delete('{{ $category->id }}')" wire:confirm="Are you sure you want to delete?" href="#"><i class="fas fa-trash pe-2"></i> Delete</a></li>
                                    </ul>
                                  </div>
                                  
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $categories->links() }}
        </div>
    </div>

</div>
