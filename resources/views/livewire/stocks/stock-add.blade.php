<div>
    @include('includes.breadcrumb', ['data' => ['Stock' => route('stocks.index'), 'AddStock' => '']])

    <div wire:loading.flex wire:target="create">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow components-section">


<form  wire:submit.prevent class="card-body">
    <h1 class="h5 mb-4">Add New Product</h1>

    <div class="row">
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label for="brand">Product Name*</label>
                <input wire:model="name" required type="text" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label class="my-1 me-2">Category*</label>
                <select wire:model="category_id" required class="form-select">
                    <option value="null" selected="selected" disabled>Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label class="my-1 me-2">Brand*</label>
                <select wire:model="brand_id" required class="form-select">
                    <option value="null" selected="selected" disabled>Select Brand</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>SKU/Barcode*</label>
                <input wire:model="sku" type="text" class="form-control form-control-sm" required>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label for="brand">Model No.</label>
                <input wire:model="model_no" type="text" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>SN/IMEI</label>
                <input wire:model="serial_no" type="text" class="form-control form-control-sm">
            </div>
        </div>
    </div>
    <div class="row rounded py-2 mb-4" style="background-color: rgb(228, 228, 228); margin-left:0.01rem;margin-right:0.01rem;">
        <div class="d-flex justify-content-between">
            <label class="pb-1">Attributes:</label>
            <button type="button" wire:click="addAttributeField" class="btn btn-white btn-sm m-0" type="button"><i class="fa-solid fa-plus"></i> Add Attribute</button>
        </div>
        @foreach ($productAttributes as $k => $productAttribute)
            <div wire:key="key_{{ $k }}" class="row px-0 mx-0">
                <div class="col-12 col-md-6">
                    <div class="mb-1">
                        <label class="small">Key</label>
                        <input wire:model="productAttributes.{{$loop->index}}.name" type="text" class="form-control small form-control-sm" placeholder="Ex: Color">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="small">Value</label>
                            @if ($k != 0)
                                <i wire:click="deleteAttributeField('{{ $k }}')" role="button" class="fa-solid fa-trash text-danger"></i>
                            @endif
                        </div>
                        <input wire:model="productAttributes.{{$loop->index}}.values" type="text" class="form-control small form-control-sm" placeholder="Ex: Green,Blue,White">
                    </div>
                </div>
            </div>            
        @endforeach

    </div>
    <div class="row">
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label for="brand">Purchase Price*</label>
                <input wire:model="purchase_price" type="text" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>Sale Price*</label> <small class="text-muted small">(Price Per Unit)</small>
                <input wire:model="price" required type="text" class="form-control form-control-sm">
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>Quantity*</label>
                <input wire:model="quantity" required type="number" class="input-date form-control form-control-sm">
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>Purchase Date</label>
                <input wire:model="purchase_date" type="date" class="input-date form-control form-control-sm">
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label for="brand">Supplier</label>
                <select wire:model="supplier_id" class="form-select">
                    <option value="null" selected="selected" disabled>Select Supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>Warranty</label>
                <select wire:model="warranty_id" class="form-select">
                    <option value="null" selected="selected" disabled>Select Warranty</option>
                    @foreach ($warranties as $warranty)
                        <option value="{{ $warranty->id }}">{{ $warranty->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>Product Description</label>
                <textarea wire:model="description" rows="1" class="form-control form-control-sm"></textarea>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>Remark</label>
                <textarea wire:model="remark" rows="1" class="form-control form-control-sm"></textarea>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="mb-4">
                <label>Status</label>
                <select wire:model="status" class="form-select">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <div>
        <button type="button" wire:click="create" class="btn btn-primary btn-sm" type="button">Add Product</button>
    </div>
</form>


            </div>
        </div>
    </div>

</div>