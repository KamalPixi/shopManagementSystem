<div>
    @include('includes.breadcrumb', ['data' => ['DB-Import-Export' => '']])

    <div wire:loading.flex wire:target="export,restore">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="card border-0 shadow components-section">
        <div class="card-body">
            <div class="d-flex justify-content-between">
        <h1 class="h5 mb-4">{{ __('Database Backup & Restore') }}</h1>
                <div>
                    <button wire:click="export" class="btn btn-primary btn-sm m-0">{{ __('Download SQLite Backup') }}</button>
                </div>
            </div>

            <p class="text-muted small">{{ __('Download a complete SQLite snapshot or restore a previously created SQLite backup.') }}</p>
            <form wire:submit.prevent="restore">
                <div class="row">
                    <div class="col">
                        <div class="mb-4">
                            <label>{{ __('Select SQLite Backup') }} <small>(.sqlite, .db)</small></label>
                            <input wire:model="file" type="file" accept=".sqlite,.sqlite3,.db" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-danger btn-sm">{{ __('Restore Database') }}</button>
                </div>
            </form>
        </div>
    </div>

</div>
