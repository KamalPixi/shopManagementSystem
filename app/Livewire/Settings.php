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
    public $settings;

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
    ];

    public function mount() {
        $shop = Shop::first();
        if ($shop) {
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
            ]);
        }
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

        if (!$shop) {
            $validated['logo'] = $logo_path ?? null;
            $validated['currency'] = ['code' => $validated['currency']];
            Shop::create($validated);
            return session()->flash('success', 'Shop settings saved.');
        }

        $validated['logo'] = $logo_path ?? null;
        $validated['currency'] = ['code' => $validated['currency']];
        $shop->update($validated);
        return session()->flash('success', 'Shop settings updated.');
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('settings:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.settings');
    }
}
