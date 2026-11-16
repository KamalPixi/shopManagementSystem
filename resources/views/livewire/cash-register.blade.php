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
        <div class="card-header bg-gray-800 text-white d-flex justify-content-between align-items-center py-2 px-3">
            <h5 class="h6 mb-0 text-white d-flex align-items-center">
                <i class="fa-solid fa-cash-register me-2 text-warning"></i> {{ __('Today\'s Register Status') }}
            </h5>
            <div>
                @if ($currentSession)
                    <span class="badge bg-success px-3 py-1 fs-7 fw-bold">{{ __('REGISTER IS OPEN') }}</span>
                @else
                    <span class="badge bg-secondary px-3 py-1 fs-7 fw-bold">{{ __('REGISTER IS CLOSED') }}</span>
                @endif
            </div>
        </div>
        <div class="card-body p-3">
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

                {{-- Compact Session Metadata Strip --}}
                <div class="bg-light border rounded px-3 py-2 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 0.78rem;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold"><i class="fa-solid fa-user-tie text-primary me-1"></i>{{ __('Opened by') }}:</span>
                        <span class="fw-bold text-dark">{{ $currentSession->user?->name ?? '-' }}</span>
                        <span class="text-muted">({{ $currentSession->opened_at->format('d/m/Y h:i A') }})</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <span class="text-muted fw-bold"><i class="fa-solid fa-vault text-success me-1"></i>{{ __('Opening Float') }}:</span>
                            <span class="fw-bold text-dark">{{ $currencySymbol }}{{ number_format($currentSession->opening_balance, 2) }}</span>
                        </div>
                        <span class="text-muted border-start ps-3">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="font-size: 0.7rem;">
                                <i class="fa-solid fa-circle fa-2xs me-1 text-success"></i>{{ __('Session #:id', ['id' => $currentSession->id]) }}
                            </span>
                        </span>
                    </div>
                </div>

                @php
                    $nonCashTotal = (float)($paymentBreakdown['card'] ?? 0) + (float)($paymentBreakdown['bank_transfer'] ?? 0) + (float)($paymentBreakdown['other'] ?? 0);
                    $totalSales = (float)($paymentBreakdown['total_sales'] ?? 0);
                @endphp

                {{-- 3 Core Executive KPI Cards (Total Shift Sales First!) --}}
                <div class="row g-2 mb-3">
                    {{-- 1. TOTAL SHIFT SALES (All Methods) --}}
                    <div class="col-md-4 col-12">
                        <div class="card border-0 shadow-sm h-100 bg-primary text-white p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-uppercase fw-bold text-white-50" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ __('Total Shift Sales') }}</span>
                                <span class="badge bg-white text-primary fw-bold px-2 py-0" style="font-size: 0.68rem;">{{ __('All Methods') }}</span>
                            </div>
                            <div class="fs-3 fw-bold my-1">
                                {{ $currencySymbol }}{{ number_format($totalSales, 2) }}
                            </div>
                            <div class="text-white-50 small" style="font-size: 0.73rem;">
                                <i class="fa-solid fa-circle-check me-1"></i>{{ __('Total customer revenue collected across all channels') }}
                            </div>
                        </div>
                    </div>

                    {{-- 2. EXPECTED CASH IN DRAWER (Physical Cash) --}}
                    <div class="col-md-4 col-12">
                        <div class="card border-0 shadow-sm h-100 bg-white border p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ __('Expected Cash in Drawer') }}</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="font-size: 0.68rem;">
                                    <i class="fa-solid fa-money-bill-wave me-1"></i>{{ __('Physical Drawer') }}
                                </span>
                            </div>
                            <div class="fs-3 fw-bold text-dark my-1">
                                {{ $currencySymbol }}{{ number_format($expectedCash, 2) }}
                            </div>
                            <div class="d-flex justify-content-between text-muted small" style="font-size: 0.73rem;">
                                <span>{{ __('Opening') }}: <strong>{{ $currencySymbol }}{{ number_format($currentSession->opening_balance, 2) }}</strong></span>
                                <span>{{ __('Cash Sales') }}: <strong class="text-success">+{{ $currencySymbol }}{{ number_format($paymentBreakdown['cash'] ?? 0, 2) }}</strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- 3. NON-CASH SALES (Electronic & Bank) --}}
                    <div class="col-md-4 col-12">
                        <div class="card border-0 shadow-sm h-100 bg-white border p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.5px;">{{ __('Non-Cash Electronic Sales') }}</span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0" style="font-size: 0.68rem;">
                                    <i class="fa-solid fa-credit-card me-1"></i>{{ __('Electronic') }}
                                </span>
                            </div>
                            <div class="fs-3 fw-bold text-info my-1">
                                {{ $currencySymbol }}{{ number_format($nonCashTotal, 2) }}
                            </div>
                            <div class="d-flex justify-content-between text-muted small" style="font-size: 0.73rem;">
                                <span>{{ __('Card') }}: <strong class="text-info">{{ $currencySymbol }}{{ number_format($paymentBreakdown['card'] ?? 0, 2) }}</strong></span>
                                <span>{{ __('Bank') }}: <strong class="text-primary">{{ $currencySymbol }}{{ number_format($paymentBreakdown['bank_transfer'] ?? 0, 2) }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shift Payment Methods & Card Terminal Reconciliation --}}
                <div class="card border-0 shadow-sm bg-white mb-3">
                    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">
                            <i class="fa-solid fa-chart-pie me-1 text-primary"></i> {{ __('Shift Revenue & Payment Methods Breakdown') }}
                        </span>
                        <span class="small text-muted" style="font-size: 0.73rem;">
                            {{ __('All Sales Today') }}: <strong class="text-dark">{{ $currencySymbol }}{{ number_format($totalSales, 2) }}</strong>
                        </span>
                    </div>
                    <div class="card-body p-2 px-3">
                        <div class="row g-2">
                            {{-- Cash Method --}}
                            <div class="col-md-3 col-6">
                                <div class="border rounded p-2 bg-light text-center h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-bold" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-money-bill-wave text-success me-1"></i> {{ __('Cash Received') }}
                                        </small>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0" style="font-size: 0.62rem;">
                                            {{ __('In Drawer') }}
                                        </span>
                                    </div>
                                    <span class="fs-5 fw-bold text-success d-block my-1">{{ $currencySymbol }}{{ number_format($paymentBreakdown['cash'] ?? 0, 2) }}</span>
                                    @if (!empty($methodMovements['cash']['in']) || !empty($methodMovements['cash']['out']))
                                        <div class="text-muted pt-1 border-top" style="font-size: 0.65rem;">
                                            {{ __('Mov') }}: <span class="text-success fw-semibold">+{{ $currencySymbol }}{{ number_format($methodMovements['cash']['in'], 2) }}</span> / <span class="text-danger fw-semibold">-{{ $currencySymbol }}{{ number_format($methodMovements['cash']['out'], 2) }}</span>
                                        </div>
                                    @else
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ __('Affects drawer count') }}</small>
                                    @endif
                                </div>
                            </div>

                            {{-- Card Terminal Method --}}
                            <div class="col-md-3 col-6">
                                <div class="border rounded p-2 bg-light text-center h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-bold" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-credit-card text-info me-1"></i> {{ __('Card Terminal Total') }}
                                        </small>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-1 py-0" style="font-size: 0.62rem;">
                                            {{ __('Terminal Batch') }}
                                        </span>
                                    </div>
                                    <span class="fs-5 fw-bold text-info d-block my-1">{{ $currencySymbol }}{{ number_format(($paymentBreakdown['card'] ?? 0) + ($methodMovements['card']['net'] ?? 0), 2) }}</span>
                                    @if (!empty($methodMovements['card']['in']) || !empty($methodMovements['card']['out']))
                                        <div class="text-muted pt-1 border-top" style="font-size: 0.65rem;">
                                            {{ __('Mov') }}: <span class="text-success fw-semibold">+{{ $currencySymbol }}{{ number_format($methodMovements['card']['in'], 2) }}</span> / <span class="text-danger fw-semibold">-{{ $currencySymbol }}{{ number_format($methodMovements['card']['out'], 2) }}</span>
                                        </div>
                                    @else
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ __('Match with POS slip') }}</small>
                                    @endif
                                </div>
                            </div>

                            {{-- Bank Transfer Method --}}
                            <div class="col-md-3 col-6">
                                <div class="border rounded p-2 bg-light text-center h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-bold" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-building-columns text-primary me-1"></i> {{ __('Bank Transfer') }}
                                        </small>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1 py-0" style="font-size: 0.62rem;">
                                            {{ __('Online / Wire') }}
                                        </span>
                                    </div>
                                    <span class="fs-5 fw-bold text-primary d-block my-1">{{ $currencySymbol }}{{ number_format(($paymentBreakdown['bank_transfer'] ?? 0) + ($methodMovements['bank_transfer']['net'] ?? 0), 2) }}</span>
                                    @if (!empty($methodMovements['bank_transfer']['in']) || !empty($methodMovements['bank_transfer']['out']))
                                        <div class="text-muted pt-1 border-top" style="font-size: 0.65rem;">
                                            {{ __('Mov') }}: <span class="text-success fw-semibold">+{{ $currencySymbol }}{{ number_format($methodMovements['bank_transfer']['in'], 2) }}</span> / <span class="text-danger fw-semibold">-{{ $currencySymbol }}{{ number_format($methodMovements['bank_transfer']['out'], 2) }}</span>
                                        </div>
                                    @else
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ __('Direct bank credits') }}</small>
                                    @endif
                                </div>
                            </div>

                            {{-- Other / Cheque Method --}}
                            <div class="col-md-3 col-6">
                                <div class="border rounded p-2 bg-light text-center h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-bold" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-ellipsis text-secondary me-1"></i> {{ __('Other / Cheque') }}
                                        </small>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1 py-0" style="font-size: 0.62rem;">
                                            {{ __('Cheque / Custom') }}
                                        </span>
                                    </div>
                                    <span class="fs-5 fw-bold text-secondary d-block my-1">{{ $currencySymbol }}{{ number_format(($paymentBreakdown['other'] ?? 0) + ($methodMovements['other']['net'] ?? 0), 2) }}</span>
                                    @if (!empty($methodMovements['other']['in']) || !empty($methodMovements['other']['out']))
                                        <div class="text-muted pt-1 border-top" style="font-size: 0.65rem;">
                                            {{ __('Mov') }}: <span class="text-success fw-semibold">+{{ $currencySymbol }}{{ number_format($methodMovements['other']['in'], 2) }}</span> / <span class="text-danger fw-semibold">-{{ $currencySymbol }}{{ number_format($methodMovements['other']['out'], 2) }}</span>
                                        </div>
                                    @else
                                        <small class="text-muted" style="font-size: 0.65rem;">{{ __('Vouchers & coupons') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Compact Action Toolbar --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-1">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('pos') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center shadow-sm">
                            <i class="fa-solid fa-cart-shopping me-1"></i> {{ __('Go to POS') }}
                        </a>
                        <a href="{{ route('moneyMovements') }}" class="btn btn-sm btn-outline-info d-inline-flex align-items-center shadow-sm">
                            <i class="fa-solid fa-money-bill-transfer me-1"></i> {{ __('Cash In / Cash Out') }}
                        </a>
                    </div>
                    <button wire:click="openCloseModal" class="btn btn-sm btn-danger px-3 py-2 d-inline-flex align-items-center shadow-sm">
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
                            @if($suggested_opening_balance > 0)
                                <small class="text-muted">{{ __('Suggested from last closing balance: :amount', ['amount' => number_format($suggested_opening_balance, 2)]) }}</small>
                            @endif
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

                        {{-- Non-Cash Payment Terminal Reconciliation --}}
                        <div class="card border border-light-subtle bg-light p-2 mb-3">
                            <span class="small fw-bold text-secondary mb-1 d-block" style="font-size: 0.72rem;">
                                <i class="fa-solid fa-calculator me-1 text-primary"></i> {{ __('Non-Cash Terminal Totals (Verify with Slip / Bank)') }}
                            </span>
                            <div class="row g-1 text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">{{ __('Card Terminal') }}</small>
                                    <strong class="text-info font-monospace">{{ $currencySymbol }}{{ number_format(($paymentBreakdown['card'] ?? 0) + ($methodMovements['card']['net'] ?? 0), 2) }}</strong>
                                    @if (!empty($methodMovements['card']['net']))
                                        <small class="text-muted d-block" style="font-size: 0.60rem;">({{ __('Sales') }} {{ number_format($paymentBreakdown['card'] ?? 0, 2) }}, {{ __('Mov') }} {{ ($methodMovements['card']['net'] >= 0 ? '+' : '') . number_format($methodMovements['card']['net'], 2) }})</small>
                                    @endif
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">{{ __('Bank Transfer') }}</small>
                                    <strong class="text-primary font-monospace">{{ $currencySymbol }}{{ number_format(($paymentBreakdown['bank_transfer'] ?? 0) + ($methodMovements['bank_transfer']['net'] ?? 0), 2) }}</strong>
                                    @if (!empty($methodMovements['bank_transfer']['net']))
                                        <small class="text-muted d-block" style="font-size: 0.60rem;">({{ __('Sales') }} {{ number_format($paymentBreakdown['bank_transfer'] ?? 0, 2) }}, {{ __('Mov') }} {{ ($methodMovements['bank_transfer']['net'] >= 0 ? '+' : '') . number_format($methodMovements['bank_transfer']['net'], 2) }})</small>
                                    @endif
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block" style="font-size: 0.68rem;">{{ __('Other / Cheque') }}</small>
                                    <strong class="text-secondary font-monospace">{{ $currencySymbol }}{{ number_format(($paymentBreakdown['other'] ?? 0) + ($methodMovements['other']['net'] ?? 0), 2) }}</strong>
                                    @if (!empty($methodMovements['other']['net']))
                                        <small class="text-muted d-block" style="font-size: 0.60rem;">({{ __('Sales') }} {{ number_format($paymentBreakdown['other'] ?? 0, 2) }}, {{ __('Mov') }} {{ ($methodMovements['other']['net'] >= 0 ? '+' : '') . number_format($methodMovements['other']['net'], 2) }})</small>
                                    @endif
                                </div>
                            </div>
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
                            <th class="text-end">{{ __('Details') }}</th>
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
                            <td class="text-end">
                                <a href="{{ route('cashRegister.details', $session) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
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
