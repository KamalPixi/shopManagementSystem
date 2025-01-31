<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use App\Models\WarrantySend;

class Statistics extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.statistics', [
            'sales' => Sale::count(),
            'stocks' => Product::count(),
            'brands' => Brand::count(),
            'categories' => Category::count(),
            'users' => User::count(),
            'warranty_send' => WarrantySend::count(),
        ]);
    }
}
