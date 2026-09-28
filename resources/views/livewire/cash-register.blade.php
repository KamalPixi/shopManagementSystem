<div>
    @include('includes.breadcrumb', ['data' => [__('Cash Register / Drawer') => '']])

    <div wire:loading.flex wire:target="startSession,endSession">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp

    {{-- Current Session Card --}}
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-gray-800 text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="h6 mb-0 text-white d-flex align-items-center">
                <i class="fa-solid fa-cash-register me-2 text-warning"></i> {{ __('Today\'s Register Status') }}
            </h5>
            <div>
                @if ($currentSession)
                    <span class="badge bg-success px-3 py-2 fs-7">{{ __('REGISTER IS OPEN') }}</span>
                @else
                    <span class="badge bg-secondary px-3 py-2 fs-7">{{ __('REGISTER IS CLOSED') }}</span>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if ($currentSession)
                @if ($currentSession->opened_at->startOfDay()->lt(now()->startOfDay()))
                    <div class="alert alert-warning border-start border-4 border-warning d-flex align-items-center mb-3 py-2 px-3" role="alert">
                        <i class="fa-solid fa-triangle-exclamation text-warning fs-4 me-3"></i>
                        <div>
                            <strong class="d-block">{{ __('Unclosed Register From Previous Day Detected!') }}</strong>
                            <span class="small">{{ __('This register was opened on :date (:time) and was not closed last night. Please count and close yesterday\'s drawer first so today\'s cash can be opened separately.', ['date' => $currentSession->opened_at->format('d M Y'), 'time' => $currentSession->opened_at->format('h:i A')]) }}</span>
                        </div>
                    </div>
                @endif
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="border rounded p-3 bg-light">
                            <small class="text-muted fw-bold d-block">{{ __('OPENED BY') }}</small>
                            <span class="fs-6 fw-bold text-dark">{{ $currentSession->user?->name ?? '-' }}</span>
                            <br><small class="text-muted">{{ $currentSession->opened_at->format('d/m/Y h:i A') }}</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="border rounded p-3 bg-light">
                            <small class="text-muted fw-bold d-block">{{ __('OPENING BALANCE') }}</small>
                            <span class="fs-5 fw-bold text-primary">{{ $currencySymbol }}{{ number_format($currentSession->opening_balance, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="border rounded p-3 bg-light">
                            <small class="text-muted fw-bold d-block">{{ __('CASH SALES TODAY') }}</small>
                            @php
                                $cashSalesTotal = $currentSession->sales()->where('payment_method', 'cash')->sum('total_price');
                            @endphp
                            <span class="fs-5 fw-bold text-success">+{{ $currencySymbol }}{{ number_format($cashSalesTotal, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="border rounded p-3 bg-light">
                            <small class="text-muted fw-bold d-block">{{ __('EXPECTED CASH IN DRAWER') }}</small>
                            <span class="fs-4 fw-bold text-dark">{{ $currencySymbol }}{{ number_format($expectedCash, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('pos') }}" class="btn btn-outline-primary me-2">
                            <i class="fa-solid fa-cart-shopping me-1"></i> {{ __('Go to POS') }}
                        </a>
                        <a href="{{ route('moneyMovements') }}" class="btn btn-outline-info">
                            <i class="fa-solid fa-money-bill-transfer me-1"></i> {{ __('Cash In / Cash Out') }}
                        </a>
                    </div>
                    <button wire:click="openCloseModal" class="btn btn-danger px-4">
                        <i class="fa-solid fa-lock me-1"></i> {{ __('Close Register (Count Drawer)') }}
                    </button>
                </div>
            @else
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fa-solid fa-cash-register fa-3x text-muted"></i>
                    </div>
                    <h5 class="fw-bold mb-2">{{ __('No Open Cash Register Session') }}</h5>
                    <p class="text-muted mb-4">{{ __('Open the cash register drawer to start tracking today\'s opening balance, cash sales, and money movements.') }}</p>
                    <button wire:click="openRegisterModal" class="btn btn-primary px-4 py-2">
                        <i class="fa-solid fa-key me-1"></i> {{ __('Open Cash Register') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal: Open Register --}}
    @if ($showOpenModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-gray-800 text-white py-3 px-4 rounded-top-3">
                    <h5 class="modal-title h6 mb-0 text-white">
                        <i class="fa-solid fa-key me-2 text-warning"></i> {{ __('Open Daily Cash Register') }}
                    </h5>
                    <button wire:click="closeOpenModal" type="button" class="btn-close btn-close-white"></button>
                </div>
                <form wire:submit.prevent="startSession">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Opening Cash Balance') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input wire:model="opening_balance" type="number" step="0.01" min="0" class="form-control form-control-lg text-end fw-bold" placeholder="0" autofocus required>
                            </div>
                            <small class="text-muted">{{ __('Count the starting cash present in the drawer.') }}</small>
                            @error('opening_balance') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ([0, 50, 100, 150, 200, 300, 500] as $preset)
                                <button type="button" wire:click="$set('opening_balance', {{ $preset }})" class="btn btn-xs btn-outline-secondary py-1 px-3">
                                    {{ $currencySymbol }}{{ $preset }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4 rounded-bottom-3 d-flex justify-content-between">
                        <button wire:click="closeOpenModal" type="button" class="btn btn-outline-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-check me-1"></i> {{ __('Open Register') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Close Register --}}
    @if ($showCloseModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-gray-800 text-white py-3 px-4 rounded-top-3">
                    <h5 class="modal-title h6 mb-0 text-white">
                        <i class="fa-solid fa-lock me-2 text-danger"></i> {{ __('Close Cash Register (End of Day)') }}
                    </h5>
                    <button wire:click="closeCloseModal" type="button" class="btn-close btn-close-white"></button>
                </div>
                <form wire:submit.prevent="endSession">
                    <div class="modal-body p-4">
                        @if ($currentSession && $currentSession->opened_at->startOfDay()->lt(now()->startOfDay()))
                            <div class="alert alert-warning py-2 px-3 mb-3 small d-flex align-items-center">
                                <i class="fa-solid fa-triangle-exclamation me-2 fs-6"></i>
                                <div>
                                    <strong>{{ __('Next-Day / Delayed Close:') }}</strong> {{ __('This session was opened on :date. Once closed, you can open today\'s fresh session.', ['date' => $currentSession->opened_at->format('d/m/Y')]) }}
                                </div>
                            </div>
                        @endif

                        <div class="alert alert-info py-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">{{ __('Calculated Expected Cash:') }}</span>
                                <span class="fs-5 fw-bold">{{ $currencySymbol }}{{ number_format($expectedCash, 2) }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Actual Counted Cash in Drawer') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input wire:model.live="closing_balance" type="number" step="0.01" min="0" class="form-control form-control-lg text-end fw-bold" placeholder="0" required>
                            </div>
                            @php
                                $diff = round((float)$closing_balance - (float)$expectedCash, 2);
                            @endphp
                            <div class="mt-2">
                                @if ($diff == 0)
                                    <span class="badge bg-success">{{ __('Cash drawer balances perfectly (Diff: 0.00)') }}</span>
                                @elseif ($diff > 0)
                                    <span class="badge bg-info text-white">{{ __('Cash Over: +') }}{{ $currencySymbol }}{{ number_format($diff, 2) }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Cash Short: ') }}{{ $currencySymbol }}{{ number_format($diff, 2) }}</span>
                                @endif
                            </div>
                            @error('closing_balance') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Closing Notes / Remarks') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                            <textarea wire:model="note" class="form-control" rows="2" placeholder="{{ __('Explain any discrepancy or add notes about today\'s shift...') }}"></textarea>
                            @error('note') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-4 rounded-bottom-3 d-flex justify-content-between">
                        <button wire:click="closeCloseModal" type="button" class="btn btn-outline-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fa-solid fa-lock me-1"></i> {{ __('Confirm & Close Register') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- History of Sessions --}}
    <div class="card border-0 shadow">
        <div class="card-body">
            <h5 class="h6 mb-3 fw-bold">{{ __('Opening & Closing Balance History') }}</h5>

            <div class="row g-2 mb-3">
                <div class="col-md-4 col-12">
                    <label class="small text-muted mb-1">{{ __('Filter by Staff') }}</label>
                    <select wire:model.live="filter_user_id" class="form-select form-select-sm">
                        <option value="">{{ __('All Staff') }}</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-12">
                    <label class="small text-muted mb-1">{{ __('Filter by Date') }}</label>
                    <input wire:model.live="filter_date" type="date" class="form-control form-control-sm">
                </div>
                <div class="col-md-4 col-12">
                    <label class="small text-muted mb-1">{{ __('Status') }}</label>
                    <select wire:model.live="filter_status" class="form-select form-select-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="open">{{ __('Open') }}</option>
                        <option value="closed">{{ __('Closed') }}</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Opened By & Time') }}</th>
                            <th class="text-end">{{ __('Opening') }}</th>
                            <th class="text-end">{{ __('Expected') }}</th>
                            <th class="text-end">{{ __('Closing (Actual)') }}</th>
                            <th class="text-center">{{ __('Difference') }}</th>
                            <th>{{ __('Closed By & Time') }}</th>
                            <th>{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $session->id }}</td>
                            <td>
                                @if ($session->status == 'open')
                                    <span class="badge bg-success">{{ __('Open') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Closed') }}</span>
                                @endif
                            </td>
                            <td>
                                <b>{{ $session->user?->name ?? '-' }}</b>
                                <br><small class="text-muted">{{ $session->opened_at->format('Y-m-d H:i') }}</small>
                            </td>
                            <td class="text-end fw-bold">{{ $currencySymbol }}{{ number_format($session->opening_balance, 2) }}</td>
                            <td class="text-end">
                                @if ($session->status == 'closed')
                                    {{ $currencySymbol }}{{ number_format($session->expected_closing_balance, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                @if ($session->status == 'closed')
                                    {{ $currencySymbol }}{{ number_format($session->closing_balance, 2) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($session->status == 'closed')
                                    @php $d = (float) $session->difference; @endphp
                                    @if ($d == 0)
                                        <span class="badge bg-success">0.00</span>
                                    @elseif ($d > 0)
                                        <span class="badge bg-info text-white">+{{ $currencySymbol }}{{ number_format($d, 2) }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $currencySymbol }}{{ number_format($d, 2) }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($session->closed_at)
                                    <b>{{ $session->closedBy?->name ?? '-' }}</b>
                                    <br><small class="text-muted">{{ $session->closed_at->format('Y-m-d H:i') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="max-width: 200px; white-space: normal;">
                                <small class="text-muted">{{ $session->note ?: '-' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">{{ __('No cash register sessions found.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
</div>
