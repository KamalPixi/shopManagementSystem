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
                                <option value="en">English (EN)</option>
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

    {{-- Cloudflare Tunnel / Internet Remote Access Section --}}
    <div class="card border-0 shadow components-section mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h5 mb-1">{{ __('Internet Remote Access (Cloudflare Tunnel)') }}</h2>
                    <p class="text-muted small mb-0">{{ __('Make your shop software accessible from anywhere via a secure, free Cloudflare Tunnel using your own domain or subdomain.') }}</p>
                </div>

                {{-- Status Badge --}}
                <div>
                    @if(!empty($cf_status['is_running']))
                        <span class="badge bg-success px-3 py-2 fs-7">
                            <i class="fas fa-check-circle me-1"></i> {{ __('Online (Running)') }}
                            @if(!empty($cf_status['pid'])) <span class="small opacity-75">(PID: {{ $cf_status['pid'] }})</span> @endif
                        </span>
                    @else
                        <span class="badge bg-secondary px-3 py-2 fs-7">
                            <i class="fas fa-times-circle me-1"></i> {{ __('Offline (Stopped)') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Live Status Alert / Info Box --}}
            @if(!empty($cf_status['is_running']) && !empty($cf_status['public_url']))
                <div class="alert alert-success d-flex align-items-center justify-content-between mb-4" role="alert">
                    <div>
                        <i class="fas fa-globe me-2"></i>
                        <strong>{{ __('Your app is live on the internet at:') }}</strong>
                        <a href="{{ $cf_status['public_url'] }}" target="_blank" class="fw-bold ms-1 text-decoration-underline text-success">
                            {{ $cf_status['public_url'] }} <i class="fas fa-external-link-alt small ms-1"></i>
                        </a>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="navigator.clipboard.writeText('{{ $cf_status['public_url'] }}'); alert('{{ __('Public URL copied to clipboard!') }}')">
                        <i class="fas fa-copy me-1"></i> {{ __('Copy Link') }}
                    </button>
                </div>
            @endif

            @if(empty($cf_status['is_installed']))
                <div class="alert alert-warning py-2 px-3 mb-3" role="alert" x-data="{ os: 'win' }">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                            <span class="small fw-semibold">{{ __('cloudflared not detected.') }}</span>
                            <span class="small text-muted ms-2 d-none d-md-inline">{{ __('Install command:') }}</span>
                        </div>

                        {{-- Compact OS switcher pills --}}
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm py-1 px-2 fw-semibold" :style="os === 'win' ? 'background-color: #0d6efd; color: #fff;' : 'background-color: #e2e8f0; color: #334155;'" @click="os = 'win'">
                                <i class="fab fa-windows me-1"></i> Windows
                            </button>
                            <button type="button" class="btn btn-sm py-1 px-2 fw-semibold" :style="os === 'mac' ? 'background-color: #0d6efd; color: #fff;' : 'background-color: #e2e8f0; color: #334155;'" @click="os = 'mac'">
                                <i class="fab fa-apple me-1"></i> macOS
                            </button>
                            <button type="button" class="btn btn-sm py-1 px-2 fw-semibold" :style="os === 'linux' ? 'background-color: #0d6efd; color: #fff;' : 'background-color: #e2e8f0; color: #334155;'" @click="os = 'linux'">
                                <i class="fab fa-linux me-1"></i> Linux
                            </button>
                        </div>
                    </div>

                    {{-- High Contrast Visible Command Box --}}
                    <div class="d-flex align-items-center justify-content-between rounded px-3 py-2 mt-2 font-monospace" style="background-color: #1e293b; color: #38bdf8; border: 1px solid #334155;">
                        <div class="text-truncate me-2 fw-semibold" style="font-size: 0.85rem; letter-spacing: 0.3px;">
                            <span x-show="os === 'win'" style="color: #38bdf8;">winget install --id Cloudflare.cloudflared</span>
                            <span x-show="os === 'mac'" style="color: #facc15;">brew install cloudflared</span>
                            <span x-show="os === 'linux'" style="color: #4ade80;">curl -L -o cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb &amp;&amp; sudo dpkg -i cloudflared.deb</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <button type="button" class="btn btn-sm btn-light py-0 px-2 fw-bold text-dark" style="font-size: 0.75rem;"
                                @click="
                                    let cmd = os === 'win' ? 'winget install --id Cloudflare.cloudflared' : (os === 'mac' ? 'brew install cloudflared' : 'curl -L -o cloudflared.deb https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64.deb && sudo dpkg -i cloudflared.deb');
                                    navigator.clipboard.writeText(cmd);
                                    alert('{{ __('Copied to clipboard!') }}');
                                ">
                                <i class="fas fa-copy me-1"></i> {{ __('Copy') }}
                            </button>
                            <button type="button" wire:click="refreshTunnelStatus" class="btn btn-sm btn-outline-light py-0 px-2" style="font-size: 0.75rem;" title="{{ __('Re-check installation') }}">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="update">
                <div class="row">
                    {{-- Enable Remote Access Toggle --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="cf_enabled" wire:model="cf_enabled">
                            <label class="form-check-label fw-bold" for="cf_enabled">
                                {{ __('Enable Remote Internet Access') }}
                            </label>
                            <div class="form-text text-muted">
                                {{ __('Allow the application to be exposed to the internet through Cloudflare.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Auto Start Toggle --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="cf_auto_start" wire:model="cf_auto_start">
                            <label class="form-check-label fw-bold" for="cf_auto_start">
                                {{ __('Auto-start Tunnel when App Boots') }}
                            </label>
                            <div class="form-text text-muted">
                                {{ __('Automatically launch the tunnel process whenever this desktop application starts.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Custom Domain --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold" for="cf_custom_domain">{{ __('Custom Domain / Subdomain') }}</label>
                        <input type="text" id="cf_custom_domain" wire:model="cf_custom_domain" class="form-control form-control-sm" placeholder="pos.yourdomain.com">
                        <div class="form-text text-muted">
                            {{ __('The public hostname configured in your Cloudflare Zero Trust tunnel (e.g. pos.myshop.com).') }}
                        </div>
                    </div>

                    {{-- Tunnel Token --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold" for="cf_tunnel_token">{{ __('Cloudflare Tunnel Token') }}</label>
                        <input type="password" id="cf_tunnel_token" wire:model="cf_tunnel_token" class="form-control form-control-sm" placeholder="eyJ... (Cloudflare Zero Trust Token)">
                        <div class="form-text text-muted">
                            {{ __('Generated in Cloudflare Zero Trust Dashboard -> Networks -> Tunnels.') }}
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i> {{ __('Save Cloudflare Settings') }}
                        </button>
                    </div>

                    {{-- Process Controls --}}
                    <div class="btn-group">
                        <button type="button" wire:click="refreshTunnelStatus" class="btn btn-outline-secondary btn-sm" title="{{ __('Refresh Status') }}">
                            <i class="fas fa-sync-alt me-1"></i> {{ __('Refresh Status') }}
                        </button>

                        @if(!empty($cf_status['is_running']))
                            <button type="button" wire:click="stopTunnel" class="btn btn-danger btn-sm">
                                <i class="fas fa-stop me-1"></i> {{ __('Stop Tunnel') }}
                            </button>
                        @else
                            <button type="button" wire:click="startTunnel" class="btn btn-success btn-sm" @if(!$cf_enabled || empty($cf_status['is_installed'])) disabled @endif>
                                <i class="fas fa-play me-1"></i> {{ __('Start Tunnel Now') }}
                            </button>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Informational Accordion / Guide --}}
            <div class="mt-4 pt-3 border-top">
                <details class="text-muted small">
                    <summary class="cursor-pointer fw-semibold text-primary">
                        <i class="fas fa-question-circle me-1"></i> {{ __('How to set up your free Cloudflare Tunnel (Step-by-Step guide)') }}
                    </summary>
                    <div class="mt-2 ps-3 border-start">
                        <ol class="mb-0">
                            <li>{{ __('Create a free account at') }} <a href="https://dash.cloudflare.com" target="_blank">dash.cloudflare.com</a> {{ __('and add your domain name.') }}</li>
                            <li>{{ __('Navigate to') }} <strong>Cloudflare Zero Trust &rarr; Networks &rarr; Tunnels</strong>.</li>
                            <li>{{ __('Click') }} <strong>{{ __('Create a Tunnel') }}</strong>, {{ __('select "Cloudflared", name it (e.g. "Shop POS"), and click Next.') }}</li>
                            <li>{{ __('Choose your operating system (Mac/Windows/Linux). Under "Install and run a connector", copy the token string after') }} <code>--token</code>.</li>
                            <li>{{ __('Paste the token above into the') }} <strong>{{ __('Cloudflare Tunnel Token') }}</strong> {{ __('field.') }}</li>
                            <li>{{ __('In Cloudflare, click Next to configure the Public Hostname: Subdomain = e.g. "pos", Domain = your domain, Service Type = HTTP, URL =') }} <code>localhost:8000</code>.</li>
                            <li>{{ __('Save settings here and click "Start Tunnel Now" (or enable Auto-start).') }}</li>
                        </ol>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>
