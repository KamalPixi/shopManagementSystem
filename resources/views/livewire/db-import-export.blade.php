<div>
    @include('includes.breadcrumb', ['data' => ['DB-Import-Export' => '']])

    <div wire:loading.flex wire:target="export,import">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="card border-0 shadow components-section">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h1 class="h5 mb-4">Database Import Export</h1>
                <div>
                    <button wire:click="export" class="btn btn-primary btn-sm m-0">Export</button>
                </div>
            </div>

            <form wire:submit.prevent="import">
                <div class="row">
                    <div class="col">
                        <div class="mb-4">
                            <label>Select File <small>(.CSV)</small></label>
                            <input wire:model="file" type="file" accept=".csv" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary btn-sm" type="button">Start Importing</button>
                </div>
            </form>
        </div>
    </div>

</div>
