<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use Illuminate\Validation\Rule;

class Customers extends Component
{
    use WithPagination;

    public $search = '';
    public $customerIdEdit;

    // form fields
    public $name, $phone_no, $email, $address, $state, $city, $postal_code, $dob;

    protected function rules()
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'phone_no'    => ['nullable', 'string', 'max:50'],
            'email'       => ['nullable', 'email', 'max:100', Rule::unique('customers', 'email')->ignore($this->customerIdEdit)],
            'address'     => ['nullable', 'string', 'max:255'],
            'state'       => ['nullable', 'string', 'max:100'],
            'city'        => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'dob'         => ['nullable', 'date'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $validated = $this->validate();
        Customer::create($validated);
        $this->reset();
        session()->flash('success', __('Customer created.'));
    }

    public function edit($id)
    {
        $c = Customer::findOrFail($id);
        $this->fill($c->toArray());
        $this->customerIdEdit = $id;
    }

    public function update()
    {
        $validated = $this->validate();
        $c = Customer::findOrFail($this->customerIdEdit);
        $c->update($validated);
        $this->reset();
        session()->flash('success', __('Customer updated.'));
    }

    public function delete($id)
    {
        if (!auth()->user()->hasPermissionTo('customer:delete')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        Customer::findOrFail($id)->delete();
        session()->flash('success', __('Customer deleted.'));
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('customer:read')) {
            return view('includes.unauthorized');
        }

        $customers = Customer::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('phone_no', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->paginate(10);

        return view('livewire.customers', [
            'customers' => $customers
        ]);
    }
}
