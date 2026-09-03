<div>
    @include('includes.breadcrumb', ['data' => ['Settings' => '']])

    <div wire:loading.flex wire:target="update">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="card border-0 shadow components-section">
        <div class="card-body">
            <h1 class="h5 mb-4">{{ __('Shop Settings') }}</h1>

            <form wire:submit.prevent="update">
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Shop Name*') }}</label>
                            <input type="text" value="{{ $name }}" readonly disabled class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Contact No.') }}</label>
                            <input wire:model="contact_no" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Email') }}</label>
                            <input wire:model="email" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Address') }}</label>
                            <input wire:model="address" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('City') }}</label>
                            <input wire:model="city" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('State') }}</label>
                            <input wire:model="state" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Postal Code') }}</label>
                            <input wire:model="postal_code" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Country') }}</label>
                            <select wire:model="country" class="form-select">
                                <option value="France">France</option>
                                <option value="Malaysia">Malaysia</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Timezone') }}</label>
                            <select wire:model="timezone" class="form-select">
                                <option value="Europe/Paris">Europe / Paris</option>
                                <option value="Asia/Kuala_Lumpur">Kuala Lumpur</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Currency') }}</label>
                            <select wire:model="currency" class="form-select">
                                <option value="EUR">EUR (€)</option>
                                <option value="MYR">MYR (RM)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ strcasecmp($country, 'France') === 0 ? 'TVA' : 'GST' }} Percentage (%)</label>
                            <input wire:model="gst_percent" type="text" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Shop Logo') }}</label>
                            <input wire:model="logo" type="file" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="mb-4">
                            <label>{{ __('Language') }}</label>
                            <select wire:model="language" class="form-select">
                                <option value="en">{{ __('English (EN)') }}</option>
                                <option value="fr">{{ __('French (FR)') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>

</div>
