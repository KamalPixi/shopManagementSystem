<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CashRegisterSession;
use App\Models\Shop;
use App\Models\User;
use App\Services\AuditLogger;

class CashRegister extends Component
{
    use WithPagination;

    public $opening_balance = 0;
    public $suggested_opening_balance = 0;
    public $closing_balance = 0;
    public $note = '';

    public $showOpenModal = false;
    public $showCloseModal = false;

    // Filters for history
    public $filter_user_id = '';
    public $filter_date = '';
    public $filter_status = '';

    public function openRegisterModal()
    {
        $lastSession = CashRegisterSession::where('status', 'closed')->latest('closed_at')->first();
        $this->suggested_opening_balance = (float) ($lastSession?->closing_balance ?? $lastSession?->expected_closing_balance ?? 0);
        $this->opening_balance = $this->suggested_opening_balance;
        $this->showOpenModal = true;
    }

    public function closeOpenModal()
    {
        $this->showOpenModal = false;
    }

    public function startSession()
    {
        $this->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $activeSession = CashRegisterSession::currentOpenSession();
        if ($activeSession) {
            session()->flash('failed', __('There is already an open register session! Please close it first.'));
            return;
        }

        $session = CashRegisterSession::create([
            'user_id' => auth()->id(),
            'opening_balance' => $this->opening_balance,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        AuditLogger::log(
            'register_opened',
            __(':user opened daily cash register with opening balance of :amount', [
                'user' => auth()->user()->name,
                'amount' => number_format($this->opening_balance, 2)
            ]),
            $session,
            ['opening_balance' => $this->opening_balance]
        );

        $this->showOpenModal = false;
        session()->flash('success', __('Cash register session opened successfully.'));
    }

    public function openCloseModal()
    {
        $activeSession = CashRegisterSession::currentOpenSession();
        if (!$activeSession) {
            session()->flash('failed', __('No active register session found to close.'));
            return;
        }

        $this->closing_balance = $activeSession->calculateExpectedCash();
        $this->note = '';
        $this->showCloseModal = true;
    }

    public function closeCloseModal()
    {
        $this->showCloseModal = false;
    }

    public function endSession()
    {
        $this->validate([
            'closing_balance' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        $activeSession = CashRegisterSession::currentOpenSession();
        if (!$activeSession) {
            session()->flash('failed', __('No active register session found to close.'));
            return;
        }

        $expected = $activeSession->calculateExpectedCash();
        $actual = (float) $this->closing_balance;
        $diff = round($actual - $expected, 2);

        $activeSession->update([
            'closing_balance' => $actual,
            'expected_closing_balance' => $expected,
            'difference' => $diff,
            'closed_by' => auth()->id(),
            'closed_at' => now(),
            'note' => $this->note,
            'status' => 'closed',
        ]);

        $isDelayed = $activeSession->opened_at->startOfDay()->lt(now()->startOfDay());
        $actionName = $isDelayed ? 'register_closed_delayed' : 'register_closed';

        AuditLogger::log(
            $actionName,
            __(':user closed cash register:opened. Expected: :exp, Actual: :act, Diff: :diff', [
                'user' => auth()->user()->name,
                'opened' => $isDelayed ? ' (Opened on ' . $activeSession->opened_at->format('d/m/Y') . ' - Next-day/delayed close)' : '',
                'exp' => number_format($expected, 2),
                'act' => number_format($actual, 2),
                'diff' => number_format($diff, 2)
            ]),
            $activeSession,
            [
                'expected' => $expected,
                'closing_balance' => $actual,
                'difference' => $diff,
                'note' => $this->note,
                'is_delayed_close' => $isDelayed,
                'opened_at' => $activeSession->opened_at->toDateTimeString(),
            ]
        );

        $this->showCloseModal = false;
        if ($isDelayed) {
            session()->flash('success', __('Previous day\'s cash register closed successfully. Please now open today\'s cash register session.'));
        } else {
            session()->flash('success', __('Cash register session closed successfully.'));
        }
    }

    public function render()
    {
        $currentSession = CashRegisterSession::currentOpenSession();
        $expectedCash = $currentSession ? $currentSession->calculateExpectedCash() : 0;

        $query = CashRegisterSession::with(['user', 'closedBy'])->latest('opened_at');

        if (!empty($this->filter_user_id)) {
            $query->where('user_id', $this->filter_user_id);
        }

        if (!empty($this->filter_date)) {
            $query->whereDate('opened_at', $this->filter_date);
        }

        if (!empty($this->filter_status)) {
            $query->where('status', $this->filter_status);
        }

        return view('livewire.cash-register', [
            'currentSession' => $currentSession,
            'expectedCash' => $expectedCash,
            'sessions' => $query->paginate(15),
            'users' => User::all(),
            'shop' => Shop::first(),
        ]);
    }
}
