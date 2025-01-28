<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WarrantySend as WarrantySendModel;
use App\Models\Product;

class WarrantyCustomerReceive extends Component
{
    use WithPagination;

    // form fields
    public $repair_cost = 0;
    public $repair_cost_paid = 0;
    public $repair_cost_due = 0;
    public $customer_receive_date;
    public $customer_receive_remark;

    public $warranty_id;

    // for filter
    public $search_reference_no;
    public $search_customer_name;

    protected $rules = [
        'repair_cost' => 'nullable|numeric',
        'repair_cost_paid' => 'nullable|numeric',
        'repair_cost_due' => 'nullable|numeric',
        'customer_receive_date' => 'nullable|date_format:Y-m-d',
        'customer_receive_remark' => 'nullable|string|max:2000',
    ];

    public function mount() {
        $this->customer_receive_date = date('Y-m-d');
    }


    public function customerReceived() {
        $validated = $this->validate();
        $w = WarrantySendModel::findOrFail($this->warranty_id);

        $validated['status'] = 'customer_received';
        $w->update($validated);

        $this->warranty_id = null;
        session()->flash('success', 'Customer has received success.');
    }
    public function customerReceive($id) {
        $this->warranty_id = $id;
        $w = WarrantySendModel::findOrFail($this->warranty_id);
        $this->fill($w->toArray());

        $this->dispatch('open-modal' , ['modal_id' => 'modal-default']);
    }

    public function render()
    {
        $query = WarrantySendModel::query();
        $query->where('status', 'customer_received');
        $query->orderBy('updated_at', 'desc');

        if (!empty($this->search_reference_no)) {
            $query->where('reference_no', $this->search_reference_no);
        }

        if (!empty($this->search_customer_name)) {
            // Query JSON field for customer_name inside customer_details
            $query->where('customer_details->customer_name', 'like', '%' . $this->search_customer_name . '%');
        }

        return view('livewire.warranty-customer-receive', [
            'warranties' => $query->paginate(20)
        ]);
    }
}
