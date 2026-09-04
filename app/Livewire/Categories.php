<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use Illuminate\Validation\Rule;

class Categories extends Component
{
    use WithPagination;

    public $name;
    public $description;

    public $categoryIdEdit;

    public function create() {
        $validated = $this->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:255',
        ]);

        Category::create($validated);
        
        $this->reset();
        session()->flash('success', __('Category created.'));
    }

    public function edit($id) {
        $b = Category::findOrFail($id);
        $this->fill([
            'name' => $b->name,
            'description' => $b->description,
        ]);

        $this->categoryIdEdit = $id;
    }

    public function update() {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($this->categoryIdEdit),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        $b = Category::findOrFail($this->categoryIdEdit);
        $b->update($validated);

        $this->reset();
        session()->flash('success', __('Category updated.'));
    }

    public function delete($id) {
        if (!auth()->user()->hasPermissionTo('category:delete')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $b = Category::findOrFail($id);
        $b->delete();
        session()->flash('success', __('Category deleted.'));
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('category:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.categories', [
            'categories' => Category::paginate(20)
        ]);
    }
}
