<div>
    @include('includes.breadcrumb', ['data' => ['Profile' => '']])

    <div wire:loading.flex wire:target="update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="card border-0 shadow components-section">
        <div class="card-body">
            <h1 class="h5 mb-4">Shop Settings</h1>

            <form wire:submit.prevent="update">
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Shop Name*</label>
                            <input wire:model="name" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Contact No.</label>
                            <input wire:model="contact_no" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Email</label>
                            <input wire:model="email" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Address</label>
                            <input wire:model="address" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>City</label>
                            <input wire:model="city" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>State</label>
                            <input wire:model="state" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Postal Code</label>
                            <input wire:model="postal_code" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Country</label>
                            <select wire:model="country" class="form-select">
                                <option value="Malaysia">Malaysia</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Timezone</label>
                            <select wire:model="timezone" class="form-select">
                                <option value="Asia/Kuala_Lumpur">Kuala Lumpur</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Currency</label>
                            <select wire:model="currency" class="form-select">
                                <option value="MYR">MYR</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>GST Percentage (%)</label>
                            <input wire:model="gst_percent" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>Shop Logo</label>
                            <input wire:model="logo" type="file" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary btn-sm" type="button">Save</button>
                </div>
            </form>
        </div>
    </div>

</div>
