<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WarrantySend as WarrantySendModel;
use App\Models\Product;

class WarrantySend extends Component
{
    use WithPagination;

    // form fields
    public $reference_no;
    public $sale_item_id;
    public $product_name;
    public $problem_description;
    public $repair_shop_details;
    public $customer_name;
    public $customer_phone_no;
    public $repair_cost = 0;
    public $repair_cost_paid = 0;
    public $repair_cost_due = 0;
    public $send_date;
    public $remark;

    public $editId;
    public $products;

    // search fields
    public $search_reference_no;
    public $search_customer_name;

    // modal warranty id
    public $mark_receive_warranty_id;
    public $receive_date;
    public $receive_remark;

    protected $rules = [
        'reference_no' => 'nullable|string|max:255',
        'sale_item_id' => 'nullable|exists:sale_items,id',
        'product_name' => 'nullable|string|max:255',
        'problem_description' => 'nullable|string|max:2000',
        'repair_shop_details' => 'nullable|string|max:255',
        'customer_name' => 'nullable|string|max:255',
        'customer_phone_no' => 'nullable|string|max:255',
        'repair_cost' => 'nullable|numeric',
        'repair_cost_paid' => 'nullable|numeric',
        'repair_cost_due' => 'nullable|numeric',
        'send_date' => 'nullable|date_format:Y-m-d',
        'remark' => 'nullable|string|max:2000',
    ];

    public function mount() {
        $this->send_date = date('Y-m-d');
        $this->products = Collect([]);
    }

    public function updated($propertyName) {
        if ($propertyName == 'product_name') {
            if (empty($this->product_name)) {
                $this->products = Collect([]);
                return;
            }
            $this->products = Product::where('name', 'like', '%' . $this->product_name .'%')->limit(10)->get();
        }
    }
    

    public function create() {
        $validated = $this->validate();

        $validated['customer_details'] = [
            'customer_name' => $validated['customer_name'],
            'customer_phone_no' => $validated['customer_phone_no'],
        ];
        $validated['status'] = 'send';
        WarrantySendModel::create($validated);
        
        $this->resetExcept('products');
        session()->flash('success', 'Warranty send success.');
    }

    public function edit($id) {
        $w = WarrantySendModel::findOrFail($id);

        $data = $w->toArray();
        $data['customer_name'] = $w->customer_details['customer_name'];
        $data['customer_phone_no'] = $w->customer_details['customer_phone_no'];
        $this->fill($data);
        $this->editId = $id;
    }

    public function update() {
        $validated = $this->validate();

        $validated['customer_details'] = [
            'customer_name' => $validated['customer_name'],
            'customer_phone_no' => $validated['customer_phone_no'],
        ];

        $b = WarrantySendModel::findOrFail($this->editId);
        $b->update($validated);

        $this->resetExcept('products');
        session()->flash('success', 'Updated.');
    }

    public function setProductName($name) {
        $this->product_name = $name;
        $this->products = Collect([]);
    }

    public function delete($id) {
        if (!auth()->user()->hasPermissionTo('warranty-send:delete')) {
            session()->flash('failed', 'Access denied!');
            return;
        }

        $w = WarrantySendModel::findOrFail($id);
        $w->delete();
        session()->flash('success', 'Warranty record deleted.');
    }


    public function markAsReceiveModal($id) {
        $this->mark_receive_warranty_id = $id;
        $this->dispatch('open-modal' , ['modal_id' => 'modal-default']);
    }

    public function markAsReceived() {
        $w = WarrantySendModel::findOrFail($this->mark_receive_warranty_id);

        $validated = $this->validate([
            'receive_date' => 'required|date_format:Y-m-d',
            'receive_remark' => 'required|string|max:2000',
        ]);
        $validated['status'] = 'received';
        $w->update($validated);
        $this->mark_receive_warranty_id = null;
        session()->flash('success', 'Marked as received ' . $w->product_name);
    }

    public function render()
    {
        if (!auth()->user()->hasPermissionTo('warranty-send:read')) {
            return view('includes.unauthorized');
        }

        $query = WarrantySendModel::query();
        $query->where('status', 'send');

        if (!empty($this->search_reference_no)) {
            $query->where('reference_no', $this->search_reference_no);
        }

        if (!empty($this->search_customer_name)) {
            // Query JSON field for customer_name inside customer_details
            $query->where('customer_details->customer_name', 'like', '%' . $this->search_customer_name . '%');
        }

        return view('livewire.warranty-send', [
            'warranties' => $query->paginate(20)
        ]);
    }
}
