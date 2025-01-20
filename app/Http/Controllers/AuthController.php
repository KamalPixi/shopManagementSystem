<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        session()->regenerate();

        return redirect()->route('login');
    }
}
