<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Brand;
use Illuminate\Validation\Rule;

class Brands extends Component
{
    use WithPagination;

    public $name;
    public $description;

    public $brandIdEdit;

    public function create() {
        $validated = $this->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string|max:255',
        ]);

        Brand::create($validated);
        
        $this->reset();
        session()->flash('success', __('Brand created.'));
    }

    public function edit($id) {
        $b = Brand::findOrFail($id);
        $this->fill([
            'name' => $b->name,
            'description' => $b->description,
        ]);

        $this->brandIdEdit = $id;
    }

    public function update() {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($this->brandIdEdit),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        $b = Brand::findOrFail($this->brandIdEdit);
        $b->update($validated);

        $this->reset();
        session()->flash('success', __('Brand updated.'));
    }

    public function delete($id) {
        if (!auth()->user()->hasPermissionTo('brand:delete')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $b = Brand::findOrFail($id);
        $b->delete();
        session()->flash('success', __('Brand deleted.'));
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('brand:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.brands', [
            'brands' => Brand::paginate(20)
        ]);
    }
}
