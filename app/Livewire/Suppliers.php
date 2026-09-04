<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use Illuminate\Validation\Rule;

class Suppliers extends Component
{
    use WithPagination;

    public $supplierIdEdit;

    public $name, $email, $phone, $contact_person;
    public $address, $city, $state, $postal_code, $country;
    public $bank_details, $notes, $website;

    protected function rules()
    {
        return [
            'name'           => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->ignore($this->supplierIdEdit)],
            'email'          => ['nullable', 'email', 'max:100'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'address'        => ['nullable', 'string', 'max:255'],
            'city'           => ['nullable', 'string', 'max:100'],
            'state'          => ['nullable', 'string', 'max:100'],
            'postal_code'    => ['nullable', 'string', 'max:50'],
            'country'        => ['nullable', 'string', 'max:100'],
            'bank_details'   => ['nullable', 'string'],
            'notes'          => ['nullable', 'string'],
            'website'        => ['nullable', 'string', 'max:255'],
        ];
    }

    public function create()
    {
        $validated = $this->validate();
        Supplier::create($validated);
        $this->reset();
        session()->flash('success', __('Supplier created.'));
    }

    public function edit($id)
    {
        $s = Supplier::findOrFail($id);
        $this->fill($s->toArray());
        $this->supplierIdEdit = $id;
    }

    public function update()
    {
        $validated = $this->validate();
        $s = Supplier::findOrFail($this->supplierIdEdit);
        $s->update($validated);
        $this->reset();
        session()->flash('success', __('Supplier updated.'));
    }

    public function delete($id)
    {
        if (!auth()->user()->hasPermissionTo('supplier:delete')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        Supplier::findOrFail($id)->delete();
        session()->flash('success', __('Supplier deleted.'));
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('supplier:read')) {
            return view('includes.unauthorized');
        }

        return view('livewire.suppliers', [
            'suppliers' => Supplier::paginate(20)
        ]);
    }
}
