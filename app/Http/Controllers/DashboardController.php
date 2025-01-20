<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard() {
        return view('dashboard');
    }

    public function pos() {
        return view('pos');
    }

    public function brands() {
        return view('brands');
    }

    public function categories() {
        return view('categories');
    }

    public function stocks() {
        return view('stocks.stocks');
    }

    public function stockAdd() {
        return view('stocks.add');
    }

    public function stockEdit() {
        return view('stocks.edit');
    }

    public function stockShow() {
        return view('stocks.show');
    }

    public function settings() {
        return view('settings');
    }
}
