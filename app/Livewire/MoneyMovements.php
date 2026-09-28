<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\MoneyMovement;
use App\Models\MoneyCategory;
use App\Models\CashRegisterSession;
use App\Models\Shop;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Str;

class MoneyMovements extends Component
{
    use WithPagination, WithFileUploads;

    // Entry Form
    public $type = 'in'; // 'in' or 'out'
    public $amount = '';
    public $money_category_id = '';
    public $reason = '';
    public $attachment;

    // Category Management Modal
    public $showCategoryModal = false;
    public $editingCategoryId = null;
    public $category_name = '';
    public $category_type = 'both';
    public $category_description = '';

    // Filters
    public $filter_type = '';
    public $filter_category_id = '';
    public $filter_user_id = '';
    public $filter_start_date = '';
    public $filter_end_date = '';

    protected function rules()
    {
        if ($this->type === 'in') {
            // Money In requires selecting a category as specified: "at money adding in business must slect the category"
            return [
                'type' => 'required|in:in,out',
                'amount' => 'required|numeric|min:0.01',
                'money_category_id' => 'required|exists:money_categories,id',
                'reason' => 'required|string|max:1000',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ];
        } else {
            // Money Out requires entering reason: "anyone can take ourt money from business and enter the resone, so admin knows who ttok moaney and why, and when"
            return [
                'type' => 'required|in:in,out',
                'amount' => 'required|numeric|min:0.01',
                'money_category_id' => 'nullable|exists:money_categories,id',
                'reason' => 'required|string|max:1000',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ];
        }
    }

    public function mount()
    {
        $this->filter_start_date = date('Y-m-01'); // start of current month
        $this->filter_end_date = date('Y-m-d');
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->money_category_id = '';
    }

    public function recordMovement()
    {
        $this->validate();

        $attachmentPath = null;
        if ($this->attachment) {
            $directory = 'uploads/movements';
            if (!is_dir(public_path($directory))) {
                mkdir(public_path($directory), 0755, true);
            }
            $ext = $this->attachment->getClientOriginalExtension();
            $filename = Str::random(40) . '.' . $ext;
            $attachmentPath = $this->attachment->storeAs($directory, $filename, 'public_direct');
        }

        $currentSession = CashRegisterSession::currentOpenSession();

        $movement = MoneyMovement::create([
            'cash_register_session_id' => $currentSession?->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'money_category_id' => !empty($this->money_category_id) ? $this->money_category_id : null,
            'reason' => trim($this->reason),
            'user_id' => auth()->id(),
            'attachment' => $attachmentPath,
        ]);

        $catName = $movement->category?->name ?? 'General';
        $action = $this->type === 'in' ? 'money_in' : 'money_out';
        $desc = $this->type === 'in'
            ? __(':user added :amount to cash (Source: :cat). Reason: :reason', [
                'user' => auth()->user()->name,
                'amount' => number_format($this->amount, 2),
                'cat' => $catName,
                'reason' => $this->reason
            ])
            : __(':user took out :amount from cash. Reason: :reason', [
                'user' => auth()->user()->name,
                'amount' => number_format($this->amount, 2),
                'reason' => $this->reason
            ]);

        AuditLogger::log($action, $desc, $movement, [
            'type' => $this->type,
            'amount' => $this->amount,
            'category' => $catName,
            'reason' => $this->reason
        ]);

        $this->reset(['amount', 'reason', 'money_category_id', 'attachment']);
        session()->flash('success', __('Money entry recorded successfully.'));
    }

    public function openCategoryModal()
    {
        $this->reset(['editingCategoryId', 'category_name', 'category_type', 'category_description']);
        $this->category_type = 'both';
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->reset(['editingCategoryId', 'category_name', 'category_type', 'category_description']);
        $this->showCategoryModal = false;
    }

    public function editCategory($id)
    {
        $cat = MoneyCategory::findOrFail($id);
        $this->editingCategoryId = $cat->id;
        $this->category_name = $cat->name;
        $this->category_type = $cat->type;
        $this->category_description = $cat->description ?? '';
    }

    public function cancelEditCategory()
    {
        $this->reset(['editingCategoryId', 'category_name', 'category_type', 'category_description']);
        $this->category_type = 'both';
    }

    public function saveCategory()
    {
        $this->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|in:in,out,both',
            'category_description' => 'nullable|string|max:500',
        ]);

        if ($this->editingCategoryId) {
            $cat = MoneyCategory::findOrFail($this->editingCategoryId);
            $oldName = $cat->name;
            $cat->update([
                'name' => trim($this->category_name),
                'type' => $this->category_type,
                'description' => trim($this->category_description),
            ]);

            AuditLogger::log(
                'updated_money_category',
                __(':user updated money category [:name]', ['user' => auth()->user()->name, 'name' => $cat->name]),
                $cat,
                ['old_name' => $oldName, 'new_name' => $cat->name, 'type' => $cat->type]
            );

            $this->cancelEditCategory();
            session()->flash('category_success', __('Money category updated successfully.'));
        } else {
            $cat = MoneyCategory::create([
                'name' => trim($this->category_name),
                'type' => $this->category_type,
                'description' => trim($this->category_description),
                'created_by' => auth()->id(),
            ]);

            AuditLogger::log(
                'created_money_category',
                __(':user created money category [:name]', ['user' => auth()->user()->name, 'name' => $cat->name]),
                $cat
            );

            $this->reset(['editingCategoryId', 'category_name', 'category_type', 'category_description']);
            $this->category_type = 'both';
            session()->flash('category_success', __('Money category created successfully.'));
        }
    }

    public function deleteCategory($id)
    {
        $cat = MoneyCategory::findOrFail($id);
        $catName = $cat->name;

        // If currently editing this category, cancel editing first
        if ($this->editingCategoryId === $id) {
            $this->cancelEditCategory();
        }

        $cat->delete();

        AuditLogger::log(
            'deleted_money_category',
            __(':user deleted money category [:name]', ['user' => auth()->user()->name, 'name' => $catName])
        );

        session()->flash('category_success', __('Category deleted successfully.'));
    }

    public function deleteMovement($id)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasPermissionTo('money-movements:delete')) {
            session()->flash('failed', __('Access denied!'));
            return;
        }

        $movement = MoneyMovement::findOrFail($id);
        $amount = $movement->amount;
        $type = $movement->type;
        $movement->delete();

        AuditLogger::log(
            'deleted_money_movement',
            __(':user deleted money entry of :amount (:type)', [
                'user' => auth()->user()->name,
                'amount' => number_format($amount, 2),
                'type' => $type
            ])
        );

        session()->flash('success', __('Entry deleted.'));
    }

    public function render()
    {
        $query = MoneyMovement::with(['user', 'category'])->latest();

        if (!empty($this->filter_type)) {
            $query->where('type', $this->filter_type);
        }

        if (!empty($this->filter_category_id)) {
            $query->where('money_category_id', $this->filter_category_id);
        }

        if (!empty($this->filter_user_id)) {
            $query->where('user_id', $this->filter_user_id);
        }

        if (!empty($this->filter_start_date)) {
            $query->whereDate('created_at', '>=', $this->filter_start_date);
        }

        if (!empty($this->filter_end_date)) {
            $query->whereDate('created_at', '<=', $this->filter_end_date);
        }

        // Totals for filtered period
        $statsQuery = clone $query;
        $totalIn = (float) (clone $statsQuery)->where('type', 'in')->sum('amount');
        $totalOut = (float) (clone $statsQuery)->where('type', 'out')->sum('amount');
        $netCash = $totalIn - $totalOut;

        $inCategories = MoneyCategory::whereIn('type', ['in', 'both'])->orderBy('name')->get();
        $outCategories = MoneyCategory::whereIn('type', ['out', 'both'])->orderBy('name')->get();
        $allCategories = MoneyCategory::orderBy('name')->get();

        return view('livewire.money-movements', [
            'movements' => $query->paginate(20),
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'netCash' => $netCash,
            'inCategories' => $inCategories,
            'outCategories' => $outCategories,
            'allCategories' => $allCategories,
            'users' => User::all(),
            'shop' => Shop::first(),
        ]);
    }
}
