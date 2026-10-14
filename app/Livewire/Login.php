<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;
use App\Models\SystemMeta;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

class Login extends Component
{
    public $username;
    public $password;
    public $remember = false;

    public function mount() {
        $this->seedDB();
    }

    public function login() {
        $validated = $this->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $remember = $this->validate([
            'remember' => 'nullable|boolean',
        ]);

        if (Auth::attempt($validated, (bool) ($remember['remember'] ?? false))) {
            session()->regenerate();
            session()->put('auth.remember', (bool) ($remember['remember'] ?? false));
            return redirect()->intended('dashboard');
        }

        session()->flash('failed', __('Invalid Credentials!'));
    }

    public function seedDB() {
        $system = SystemMeta::first();
        if (!$system) {
            $seeder = new DatabaseSeeder();
            $seeder->run();
            session()->flash('success', __('Database seed completed.'));
            return;
        }
    }

    public function render()
    {
        return view('livewire.login');
    }
}
