<div class="card border-0 shadow mb-4">
    <div class="card-body">
        <h6 class="mb-3">Low Stock Products</h6>
        <div class="mb-4 table-responsive">
            <table class="table table-centered table-nowrap mb-0 rounded">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 8%;" class="border-0 rounded-start">#</th>
                        <th class="border-0">Name</th>
                        <th class="border-0">SKU</th>
                        <th class="border-0">Category</th>
                        <th style="width: 8%;" class="border-0">Quantity</th>
                        <th style="width: 8%;" class="border-0 text-end rounded-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                    <tr wire:key="tr_key_{{ $product->id }}">
                        <td>{{ $products->firstItem() + $loop->index }}</td>
                        <td class="fw-bold align-items-center">
                            <div class="d-flex align-items-center">
                                {{ $product->name }}
                            </div>
                        </td>
                        <td class="fw-bold align-items-center"> {{ $product->sku ?? 'N/A' }} </td>
                        <td class="fw-bold align-items-center"> {{ $product->category->name ?? 'Uncategorized' }} </td>
                        <td class="fw-bold align-items-center text-center">
                            @if($product->quantity < 5)
                                <span class="badge bg-danger">{{ $product->quantity }}</span>
                            @else
                                <span class="badge bg-warning">{{ $product->quantity }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                    <li><a class="dropdown-item" href=""><i class="fas fa-edit pe-2"></i> Edit</a></li>
                                    <li><a class="dropdown-item text-info" href=""><i class="fas fa-eye pe-2"></i> View</a></li>
                                    <li><a class="dropdown-item text-danger" href="" onclick="return confirm('Are you sure?')"><i class="fas fa-trash pe-2"></i> Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No low-stock products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links() }}
    </div>
</div>
