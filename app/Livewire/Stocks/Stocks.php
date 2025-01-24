<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Product;

class Stocks extends Component
{
    use WithPagination;

    public $name;
    public $sku;
    public $model_no;
    public $category_id = '';

    public function render()
    {
        $query = Product::query();
        $query->orderBy('created_at', 'desc');
        
        if (!empty($this->name)) {
            $query->where('name', 'like', '%' . $this->name . '%');
        }
        if (!empty($this->sku)) {
            $query->where('sku', 'like', '%' . $this->sku . '%');
        }
        if (!empty($this->model_no)) {
            $query->where('model_no', 'like', '%' . $this->model_no . '%');
        }
        if (!empty($this->category_id)) {
            $query->where('category_id', $this->category_id);
        }

        return view('livewire.stocks.stocks', [
            'categories' => Category::all(),
            'products' => $query->paginate(50),
        ]);
    }
}
