<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditLog;
use App\Models\User;

class AuditLogs extends Component
{
    use WithPagination;

    public $filter_user_id = '';
    public $filter_action = '';
    public $filter_date = '';
    public $search = '';

    public function clearFilters()
    {
        $this->reset(['filter_user_id', 'filter_action', 'filter_date', 'search']);
    }

    public function render()
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->hasPermissionTo('audit-logs:read')) {
            return view('includes.unauthorized');
        }

        $query = AuditLog::with('user')->latest('id');

        if (!empty($this->filter_user_id)) {
            $query->where('user_id', $this->filter_user_id);
        }

        if (!empty($this->filter_action)) {
            $query->where('action', 'like', '%' . $this->filter_action . '%');
        }

        if (!empty($this->filter_date)) {
            $query->whereDate('created_at', $this->filter_date);
        }

        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                  ->orWhere('ip_address', 'like', $term);
            });
        }

        return view('livewire.audit-logs', [
            'logs' => $query->paginate(25),
            'users' => User::all(),
        ]);
    }
}
