<div>
    @include('includes.breadcrumb', ['data' => [__('Audit Logs (User Activities)') => '']])

    <div wire:loading.flex>
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-gray-800 text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="h6 mb-0 text-white d-flex align-items-center">
                <i class="fa-solid fa-clock-rotate-left me-2 text-warning"></i> {{ __('Audit Trail - Actions Done by Staff & Admin') }}
            </h5>
            <span class="badge bg-primary">{{ __('Live Tracking') }}</span>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <div class="row g-2 mb-4">
                <div class="col-md-3 col-6">
                    <label class="small text-muted mb-1">{{ __('Filter by Staff / User') }}</label>
                    <select wire:model.live="filter_user_id" class="form-select form-select-sm">
                        <option value="">{{ __('All Users') }}</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <label class="small text-muted mb-1">{{ __('Filter by Action') }}</label>
                    <select wire:model.live="filter_action" class="form-select form-select-sm">
                        <option value="">{{ __('All Actions') }}</option>
                        <option value="sale">{{ __('Sales') }}</option>
                        <option value="product">{{ __('Products / Stock') }}</option>
                        <option value="money">{{ __('Money In / Out') }}</option>
                        <option value="register">{{ __('Cash Register') }}</option>
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <label class="small text-muted mb-1">{{ __('Date') }}</label>
                    <input wire:model.live="filter_date" type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 col-6">
                    <label class="small text-muted mb-1">{{ __('Search Keyword') }}</label>
                    <div class="input-group input-group-sm">
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm" placeholder="{{ __('Search descriptions, IP...') }}">
                        @if ($filter_user_id || $filter_action || $filter_date || $search)
                            <button wire:click="clearFilters" class="btn btn-outline-secondary" title="{{ __('Clear') }}">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Audit Logs Table --}}
            <div class="table-responsive">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 15%;">{{ __('Timestamp') }}</th>
                            <th style="width: 15%;">{{ __('Performed By') }}</th>
                            <th style="width: 12%;">{{ __('Action') }}</th>
                            <th style="width: 40%;">{{ __('Description / Activity') }}</th>
                            <th style="width: 13%;">{{ __('IP Address') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                <span class="fw-bold">{{ $log->created_at->format('Y-m-d') }}</span>
                                <br><small class="text-muted">{{ $log->created_at->format('h:i:s A') }} ({{ $log->created_at->diffForHumans() }})</small>
                            </td>
                            <td>
                                @if ($log->user)
                                    <b>{{ $log->user->name }}</b>
                                    <br><small class="badge bg-light text-dark border">{{ $log->user->username }} ({{ $log->user->user_type }})</small>
                                @else
                                    <span class="text-muted">{{ __('System / Guest') }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = 'bg-secondary';
                                    if (str_contains($log->action, 'sale')) $badgeClass = 'bg-success';
                                    elseif (str_contains($log->action, 'money_in')) $badgeClass = 'bg-primary';
                                    elseif (str_contains($log->action, 'money_out')) $badgeClass = 'bg-danger';
                                    elseif (str_contains($log->action, 'register')) $badgeClass = 'bg-warning text-dark';
                                    elseif (str_contains($log->action, 'product')) $badgeClass = 'bg-info text-white';
                                @endphp
                                <span class="badge {{ $badgeClass }} text-uppercase" style="font-size: 0.72rem;">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>
                            <td style="white-space: normal; line-height: 1.4;">
                                {{ $log->description }}
                                @if (!empty($log->details))
                                    <details class="mt-1">
                                        <summary class="small text-primary cursor-pointer" style="cursor: pointer;">{{ __('View Details JSON') }}</summary>
                                        <pre class="bg-light p-2 rounded small mt-1 mb-0 border" style="font-size: 0.7rem;">{{ json_encode($log->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted font-monospace">{{ $log->ip_address ?: '-' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">{{ __('No audit log records found.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
