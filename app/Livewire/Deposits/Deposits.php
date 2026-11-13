<?php

namespace App\Livewire\Deposits;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Deposit;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\PaymentMethod;
use App\Services\DepositService;
use App\Services\PrinterManagerService;
use App\Services\AuditLogger;

class Deposits extends Component
{
    use WithPagination;

    // Search and Filters
    public $search = '';
    public $status_filter = '';
    public $date_from = '';
    public $date_to = '';

    // Modal state
    public $showModal = false;
    public $editId = null;

    // Success / Print language selection overlay
    public $showSuccessModal = false;
    public $lastDepositId = null;
    public $lastReferenceNo = null;
    public $lastCustomerName = null;
    public $lastDepositAmount = null;
    public $lastRemainingBalance = null;

    // Form inputs
    public $customer_id = null;
    public $customer_name = '';
    public $customer_phone = '';
    public $customer_email = '';
    public $item_description = '';
    public $total_amount = '';
    public $deposit_amount = '';
    public $remaining_balance = 0;
    public $payment_method = 'cash';
    public $payment_reference = '';
    public $notes = '';
    public $status = 'pending';

    // Customer suggestions
    public $customer_suggestions = [];
    public $show_customer_suggestions = false;

    protected function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:100',
            'item_description' => 'required|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,completed,cancelled',
        ];
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'customer_name') {
            $trimmed = trim($this->customer_name);
            if (strlen($trimmed) >= 2) {
                $this->customer_suggestions = Customer::where('name', 'like', "%{$trimmed}%")
                    ->orWhere('phone_no', 'like', "%{$trimmed}%")
                    ->take(5)
                    ->get()
                    ->toArray();
                $this->show_customer_suggestions = !empty($this->customer_suggestions);
            } else {
                $this->customer_suggestions = [];
                $this->show_customer_suggestions = false;
            }
        }

        if (in_array($propertyName, ['total_amount', 'deposit_amount'], true)) {
            $this->calculateBalance();
        }
    }

    public function calculateBalance(): void
    {
        $total = (float) ($this->total_amount ?: 0);
        $deposit = (float) ($this->deposit_amount ?: 0);
        $this->remaining_balance = number_format(max(0, $total - $deposit), 2, '.', '');
    }

    public function selectCustomer(int $id): void
    {
        $customer = Customer::find($id);
        if ($customer) {
            $this->customer_id = $customer->id;
            $this->customer_name = $customer->name;
            $this->customer_phone = $customer->phone_no;
            $this->customer_email = $customer->email;
        }
        $this->show_customer_suggestions = false;
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset([
            'editId',
            'customer_id',
            'customer_name',
            'customer_phone',
            'customer_email',
            'item_description',
            'total_amount',
            'deposit_amount',
            'remaining_balance',
            'payment_method',
            'payment_reference',
            'notes',
            'status',
            'customer_suggestions',
            'show_customer_suggestions',
        ]);

        $this->payment_method = 'cash';
        $this->status = 'pending';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $deposit = Deposit::findOrFail($id);

        $this->editId = $deposit->id;
        $this->customer_id = $deposit->customer_id;
        $this->customer_name = $deposit->customer_name;
        $this->customer_phone = $deposit->customer_phone;
        $this->customer_email = $deposit->customer_email;
        $this->item_description = $deposit->item_description;
        $this->total_amount = $deposit->total_amount;
        $this->deposit_amount = $deposit->deposit_amount;
        $this->remaining_balance = $deposit->remaining_balance;
        $this->payment_method = $deposit->payment_method;
        $this->payment_reference = $deposit->payment_reference;
        $this->notes = $deposit->notes;
        $this->status = $deposit->status;

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->show_customer_suggestions = false;
    }

    public function saveDeposit(): void
    {
        $validated = $this->validate();
        $this->calculateBalance();

        // Auto-create customer if customer doesn't exist and phone is provided
        if (!$this->customer_id && !empty($this->customer_name)) {
            $existing = Customer::where('name', $this->customer_name)
                ->when($this->customer_phone, fn($q) => $q->where('phone_no', $this->customer_phone))
                ->first();

            if ($existing) {
                $this->customer_id = $existing->id;
            } else {
                $newCustomer = Customer::create([
                    'name' => $this->customer_name,
                    'phone_no' => $this->customer_phone,
                    'email' => $this->customer_email,
                ]);
                $this->customer_id = $newCustomer->id;
            }
        }

        $data = [
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'item_description' => $this->item_description,
            'total_amount' => (float) $this->total_amount,
            'deposit_amount' => (float) $this->deposit_amount,
            'remaining_balance' => (float) $this->remaining_balance,
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'notes' => $this->notes,
            'status' => $this->status,
        ];

        if ($this->editId) {
            $deposit = Deposit::findOrFail($this->editId);
            $oldValues = $deposit->toArray();
            $data['updated_by'] = auth()->id();
            $deposit->update($data);

            AuditLogger::log(
                'deposit_updated',
                "Deposit receipt #{$deposit->reference_no} updated",
                $deposit,
                ['old' => $oldValues, 'new' => $deposit->fresh()->toArray()]
            );

            session()->flash('message', __('Deposit receipt #:ref updated successfully.', ['ref' => $deposit->reference_no]));
        } else {
            $data['reference_no'] = Deposit::generateNextReferenceNo();
            $data['created_by'] = auth()->id();
            $deposit = Deposit::create($data);

            AuditLogger::log(
                'deposit_created',
                "Deposit receipt #{$deposit->reference_no} created for {$deposit->customer_name} (Deposit: €{$deposit->deposit_amount})",
                $deposit,
                $deposit->toArray()
            );

            session()->flash('message', __('Deposit receipt #:ref created successfully.', ['ref' => $deposit->reference_no]));

            // Prompt staff for print language / receipt choices instead of auto-printing a blind specific language
            $this->lastDepositId = $deposit->id;
            $this->lastReferenceNo = $deposit->reference_no;
            $this->lastCustomerName = $deposit->customer_name;
            $this->lastDepositAmount = number_format((float) $deposit->deposit_amount, 2);
            $this->lastRemainingBalance = number_format((float) $deposit->remaining_balance, 2);
            $this->showSuccessModal = true;
        }

        $this->closeModal();
    }

    public function dismissDepositSuccess(): void
    {
        $this->showSuccessModal = false;
        $this->lastDepositId = null;
        $this->lastReferenceNo = null;
        $this->lastCustomerName = null;
        $this->lastDepositAmount = null;
        $this->lastRemainingBalance = null;
    }

    public function markCompleted(int $id): void
    {
        $deposit = Deposit::findOrFail($id);
        $oldStatus = $deposit->status;
        $deposit->update([
            'status' => 'completed',
            'remaining_balance' => 0.00,
            'updated_by' => auth()->id(),
        ]);

        AuditLogger::log(
            'deposit_completed',
            "Deposit #{$deposit->reference_no} marked as completed / fully settled.",
            $deposit,
            ['status' => 'completed', 'previous_status' => $oldStatus, 'remaining_balance' => 0.00]
        );

        session()->flash('message', __('Deposit #:ref marked as completed and fully settled.', ['ref' => $deposit->reference_no]));
    }

    public function deleteDeposit(int $id): void
    {
        $deposit = Deposit::findOrFail($id);
        $ref = $deposit->reference_no;

        AuditLogger::log(
            'deposit_deleted',
            "Deposit receipt #{$ref} deleted.",
            $deposit,
            $deposit->toArray()
        );

        $deposit->delete();
        session()->flash('message', __('Deposit receipt #:ref deleted successfully.', ['ref' => $ref]));
    }

    public function printDepositReceipt(int $id, ?string $locale = null): void
    {
        $s = new DepositService();
        $url = $s->generateDepositReceipt($id, $locale);
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function printDepositReceiptPos(int $id, ?string $locale = null): void
    {
        $deposit = Deposit::findOrFail($id);
        $printerManager = app(PrinterManagerService::class);
        $result = $printerManager->printPosDepositReceipt($deposit, $locale);

        try {
            $s = new DepositService();
            $url = $s->generateDepositReceipt($id, $locale);
            $this->dispatch('print-pdf', ['url' => $url]);
        } catch (\Throwable $e) {
            // Ignore preview fallback error
        }

        session()->flash($result['success'] ? 'message' : 'failed', $result['message']);
    }

    public function printDepositReceiptA4(int $id, ?string $locale = null): void
    {
        $s = new DepositService();
        $url = $s->generateDepositReceiptA4($id, $locale);
        $this->dispatch('print-pdf', ['url' => $url]);
    }

    public function render()
    {
        if (auth()->check() && !auth()->user()->hasRole('admin') && !auth()->user()->hasPermissionTo('deposits:read')) {
            return view('includes.unauthorized');
        }

        $query = Deposit::with(['customer', 'createdBy'])->orderBy('created_at', 'desc');

        if (!empty($this->search)) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('reference_no', 'like', "%{$term}%")
                  ->orWhere('customer_name', 'like', "%{$term}%")
                  ->orWhere('customer_phone', 'like', "%{$term}%")
                  ->orWhere('item_description', 'like', "%{$term}%");
            });
        }

        if (!empty($this->status_filter)) {
            $query->where('status', $this->status_filter);
        }

        if (!empty($this->date_from)) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if (!empty($this->date_to)) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        $deposits = $query->paginate(20);

        // Summary KPI Metrics
        $totalPendingCount = Deposit::where('status', 'pending')->count();
        $totalDepositCollected = Deposit::where('status', 'pending')->sum('deposit_amount');
        $totalRemainingDue = Deposit::where('status', 'pending')->sum('remaining_balance');
        $totalCompletedCount = Deposit::where('status', 'completed')->count();

        $shop = Shop::first();
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return view('livewire.deposits.deposits', [
            'deposits' => $deposits,
            'totalPendingCount' => $totalPendingCount,
            'totalDepositCollected' => $totalDepositCollected,
            'totalRemainingDue' => $totalRemainingDue,
            'totalCompletedCount' => $totalCompletedCount,
            'shop' => $shop,
            'paymentMethods' => $paymentMethods,
        ]);
    }
}
