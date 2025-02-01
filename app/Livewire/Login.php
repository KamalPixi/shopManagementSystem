<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;


class Login extends Component
{
    public $username;
    public $password;
    public $remember = false;

    public function login() {
        $validated = $this->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $remember = $this->validate([
            'remember' => 'nullable|boolean',
        ]);

        if (Auth::attempt($validated, $remember['remember'])) {
            session()->regenerate();
            return redirect()->intended('dashboard');
        }

        session()->flash('failed', 'Invalid Credentials!');
    }

    public function render()
    {
        return view('livewire.login');
    }
}
