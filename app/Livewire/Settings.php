<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Shop;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class Settings extends Component
{
    use WithFileUploads;

    public $name;
    public $logo = '';
    public $existing_logo = '';
    public $address;
    public $state;
    public $city;
    public $postal_code;
    public $country = 'Malaysia';
    public $contact_no;
    public $email;
    public $gst_percent = 6;
    public $currency = 'MYR';
    public $timezone = 'Asia/Kuala_Lumpur';
    public $language = 'en';
    public $settings;
    public $invoice_legal_text = "SAS au capital de 2 010 €\n{address}\nSIREN : 109 915 405\nTVA intracommunautaire: FR75 109915405";

    // Cloudflare Tunnel Properties
    public $cf_enabled = false;
    public $cf_auto_start = false;
    public $cf_custom_domain = '';
    public $cf_tunnel_token = '';
    public $cf_status = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'contact_no' => 'nullable|string|max:255',
        'logo' => 'nullable|mimes:jpg,jpeg,png|max:1024',
        'address' => 'nullable|string|max:255',
        'state' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:255',
        'postal_code' => 'nullable|string|max:10',
        'country' => 'nullable|string|max:100',
        'email' => 'nullable|string|max:255',
        'gst_percent' => 'nullable|numeric',
        'currency' => 'nullable|string|max:3',
        'timezone' => 'nullable|string|max:100',
        'language' => 'nullable|string|in:en,fr',
        'invoice_legal_text' => 'nullable|string|max:2000',
        'cf_enabled' => 'nullable|boolean',
        'cf_auto_start' => 'nullable|boolean',
        'cf_custom_domain' => 'nullable|string|max:255',
        'cf_tunnel_token' => 'nullable|string|max:500',
    ];

    public function mount() {
        $shop = Shop::first();
        if ($shop) {
            $shopLang = $shop->settings['language'] ?? (strcasecmp($shop->country ?? '', 'France') === 0 ? 'fr' : 'en');
            $this->language = session('locale', $shopLang);

            $cf = $shop->settings['cloudflare'] ?? [];
            $this->cf_enabled = (bool) ($cf['enabled'] ?? false);
            $this->cf_auto_start = (bool) ($cf['auto_start'] ?? false);
            $this->cf_custom_domain = $cf['custom_domain'] ?? '';
            $this->cf_tunnel_token = $cf['tunnel_token'] ?? '';
            $this->invoice_legal_text = $shop->settings['invoice_legal_text'] ?? $this->invoice_legal_text;

            $this->fill([
                'name' => $shop->name,
                'contact_no' => $shop->contact_no,
                'address' => $shop->address,
                'state' => $shop->state,
                'city' => $shop->city,
                'postal_code' => $shop->postal_code,
                'country' => $shop->country,
                'email' => $shop->email,
                'gst_percent' => $shop->gst_percent,
                'currency' => $shop->currency['code'],
                'timezone' => $shop->timezone,
                'existing_logo' => $shop->logo,
            ]);
        }

        $this->refreshTunnelStatus();
    }

    public function update() {
        $validated = $this->validate();
        $shop = Shop::first();

        // store logo id provided
        $logo_path = null;
        $directory = 'uploads';
        
        if($this->logo) {
            if (!is_dir(public_path($directory))) {
                mkdir(public_path($directory), 0755, true);
            }
            $extension = $this->logo->getClientOriginalExtension(); // Get file extension
            $fileName = Str::random(40) . '.' . $extension;
            $logo_path = $this->logo->storeAs($directory, $fileName, 'public_direct');
        }

        $currencyMap = [
            'EUR' => ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
            'MYR' => ['code' => 'MYR', 'symbol' => 'RM', 'name' => 'Malaysian Ringgit'],
        ];
        $selectedCurrency = $currencyMap[$validated['currency']] ?? ['code' => $validated['currency'], 'symbol' => $validated['currency'], 'name' => $validated['currency']];

        $shopSettings = $shop?->settings ?? [];
        $shopSettings['language'] = $validated['language'] ?? 'en';
        $shopSettings['cloudflare'] = [
            'enabled' => (bool) $this->cf_enabled,
            'auto_start' => (bool) $this->cf_auto_start,
            'custom_domain' => trim($this->cf_custom_domain ?? ''),
            'tunnel_token' => trim($this->cf_tunnel_token ?? ''),
        ];
        $shopSettings['invoice_legal_text'] = trim($validated['invoice_legal_text'] ?? '');
        $validated['settings'] = $shopSettings;

        // Apply immediately to current user session & app
        session(['locale' => $shopSettings['language']]);
        app()->setLocale($shopSettings['language']);

        if (!$shop) {
            $validated['logo'] = $logo_path ?? null;
            $validated['currency'] = $selectedCurrency;
            $shop = Shop::create($validated);
        } else {
            // Keep existing logo if no new logo was uploaded
            $validated['logo'] = $logo_path ?: $shop->logo;
            $validated['currency'] = $selectedCurrency;
            $shop->update($validated);
        }

        // Auto-stop tunnel if internet availability was disabled
        $tunnelService = app(\App\Services\CloudflareTunnelService::class);
        if (!$this->cf_enabled && $tunnelService->isRunning()) {
            $tunnelService->stop();
        }

        session()->flash('success', __('Shop settings updated.'));
        $this->refreshTunnelStatus();
        return $this->redirect(route('settings'), navigate: false);
    }

    public function refreshTunnelStatus()
    {
        $this->cf_status = app(\App\Services\CloudflareTunnelService::class)->getStatus();
    }

    public function startTunnel()
    {
        if (!$this->cf_enabled) {
            session()->flash('error', __('Please enable Remote Access first and save settings.'));
            return;
        }

        $result = app(\App\Services\CloudflareTunnelService::class)->start();
        if ($result['success']) {
            session()->flash('success', __($result['message']));
        } else {
            session()->flash('error', __($result['message']));
        }

        $this->refreshTunnelStatus();
    }

    public function stopTunnel()
    {
        $result = app(\App\Services\CloudflareTunnelService::class)->stop();
        session()->flash('success', __($result['message']));
        $this->refreshTunnelStatus();
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('settings:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.settings');
    }
}
