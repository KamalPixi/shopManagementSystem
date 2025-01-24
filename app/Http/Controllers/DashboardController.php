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

    public function warranties() {
        return view('warranties');
    }

    public function warrantySend() {
        return view('warranty-send');
    }

    public function stocks() {
        return view('stocks.stocks');
    }

    public function stockAdd() {
        return view('stocks.add');
    }

    public function addToStock() {
        return view('stocks.add-to-stock');
    }

    public function stockEdit() {
        return view('stocks.edit');
    }

    public function stockShow() {
        return view('stocks.show');
    }

    public function sales() {
        return view('sales.sales');
    }

    public function settings() {
        return view('settings');
    }
}
