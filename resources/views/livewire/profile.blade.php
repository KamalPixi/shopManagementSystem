<div>
    @include('includes.breadcrumb', ['data' => ['Profile' => '']])

    <div wire:loading.flex wire:target="update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="card border-0 shadow components-section">
        <div class="card-body">
            <h1 class="h5 mb-4">Profile</h1>

            <form wire:submit.prevent="update">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-4">
                            <label>Username*</label>
                            <input value="{{ $username }}" type="text" readonly class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-4">
                            <label>User Type*</label>
                            <input value="{{ $user_type }}" type="text" readonly class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-4">
                            <label>Your Name*</label>
                            <input wire:model="name" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-4">
                            <label>Password <small class="text-muted" style="font-size: 0.6rem;">(Enter new password, if want to change)</small> </label>
                            <input wire:model="password" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary btn-sm" type="button">Update</button>
                </div>
            </form>
        </div>
    </div>

</div>
