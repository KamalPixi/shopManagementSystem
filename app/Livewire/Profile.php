<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;


class Profile extends Component
{

    public $name;
    public $password;

    protected $rules = [
        'name' => 'required|string|max:255',
        'password' => 'nullable|string|max:255',
    ];

    public function mount() {
        $this->fill([
            'name' => auth()->user()?->name,
        ]);
    }

    public function update() {
        $validated = $this->validate();

        if (!empty($this->password)) {
            $validated['password'] = Hash::make($validated['password']);
        }

        auth()->user()->update($validated);
        return session()->flash('success', __('Profile updated.'));
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.profile', [
            'username' => $user?->username,
            'user_type' => $user?->user_type
        ]);
    }
}
