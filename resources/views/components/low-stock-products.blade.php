<div class="col-12">
    <div class="card">
        <!-- Card header -->
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-8">
                    <h3 class="mb-0">Low Stock Products</h3>
                </div>
                <div class="col-4 text-right">
                    <span class="badge badge-danger badge-lg">{{ $lowStockCount ?? 0 }} items</span>
                </div>
            </div>
        </div>
        <!-- Light table -->
        <div class="table-responsive">
            <table class="table align-middle table-flush table-hover">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">SKU</th>
                        <th scope="col">Category</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Price</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody class="list">
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm rounded-circle bg-warning me-2">
                                        <i class="fas fa-box text-white"></i>
                                    </span>
                                    <div>
                                        <p class="mb-0 text-sm">{{ $product->name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs font-weight-bold">{{ $product->sku ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="text-xs font-weight-bold">{{ $product->category->name ?? 'Uncategorized' }}</span>
                            </td>
                            <td>
                                @if($product->quantity < 5)
                                    <span class="badge badge-danger">{{ $product->quantity }}</span>
                                @else
                                    <span class="badge badge-warning">{{ $product->quantity }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-xs font-weight-bold">${{ number_format($product->price, 2) }}</span>
                            </td>
                            <td class="text-right">
                                <div class="actions">
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">View</a>
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
    </div>
</div>
