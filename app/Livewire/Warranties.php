<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Warranty;
use Illuminate\Validation\Rule;

class Warranties extends Component
{
    use WithPagination;

    public $name;
    public $description;

    public $editId;

    public function create() {
        $validated = $this->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string|max:255',
        ]);

        Warranty::create($validated);
        
        $this->reset();
        session()->flash('success', __('Warranty created.'));
    }

    public function edit($id) {
        $w = Warranty::findOrFail($id);
        $this->fill([
            'name' => $w->name,
            'description' => $w->description,
        ]);

        $this->editId = $id;
    }

    public function update() {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('warranties', 'name')->ignore($this->editId),
            ],
            'description' => 'nullable|string|max:255',
        ]);

        $b = Warranty::findOrFail($this->editId);
        $b->update($validated);

        $this->reset();
        session()->flash('success', __('Warranty updated.'));
    }

    public function delete($id) {
        if (!auth()->user()->hasPermissionTo('warranties:delete')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $b = Warranty::findOrFail($id);
        $b->delete();
        session()->flash('success', __('Warranty deleted.'));
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('warranties:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.warranties', [
            'warranties' => Warranty::paginate(20)
        ]);
    }
}
