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

    public function warrantyReceive() {
        return view('warranty-receive');
    }

    public function warrantyCustomerReceive() {
        return view('warranty-customer-receive');
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

    public function stockEdit($product_id) {
        return view('stocks.edit', compact('product_id'));
    }

    public function stockShow($product_id) {
        return view('stocks.show', compact('product_id'));
    }

    public function sales() {
        return view('sales.sales');
    }

    public function settings() {
        return view('settings');
    }

    public function users() {
        return view('users');
    }

    public function profile() {
        return view('profile');
    }
}
