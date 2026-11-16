<div>
    @include('includes.breadcrumb', ['data' => [__('Money Movements') => '']])

    <div wire:loading.flex wire:target="recordMovement,saveCategory,deleteMovement,addUnassignedToDrawer,printReport">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp

    @if ($unassignedCount > 0)
        <div class="alert alert-warning py-2 px-3 mb-3 small d-flex justify-content-between align-items-center">
            <div>
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                {{ __(':count cash movement(s) are not assigned to a register and are excluded from the drawer balance.', ['count' => $unassignedCount]) }}
            </div>
        </div>
    @endif

    {{-- Clean Page Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div>
            <h1 class="h5 fw-bold text-dark mb-0">{{ __('Money Movements (Cash In / Cash Out)') }}</h1>
            <p class="text-muted small mb-0">{{ __('Record and audit external cash injections, owner withdrawals, and shop expenses.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button wire:click="openCategoryModal" type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center shadow-sm">
                <i class="fa-solid fa-tags me-1 text-primary"></i> {{ __('Manage Categories') }}
            </button>
            <a href="{{ route('cashRegister') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center shadow-sm">
                <i class="fa-solid fa-cash-register me-1"></i> {{ $currentSession ? __('Cash Register') : __('Open Register') }}
            </a>
        </div>
    </div>

    {{-- Single-Line Financial Summary Ticker --}}
    <div class="bg-white border rounded px-3 py-2 mb-2 shadow-sm d-flex justify-content-between align-items-center flex-wrap gap-2" style="font-size: 0.78rem;">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="text-muted fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                <i class="fa-solid fa-chart-line text-primary me-1"></i>{{ __('Period Totals') }}:
            </span>
            <div>
                <span class="text-muted small">{{ __('Total In') }}:</span>
                <strong class="text-success ms-1">+{{ $currencySymbol }}{{ number_format($totalIn, 2) }}</strong>
            </div>
            <div class="border-start ps-3">
                <span class="text-muted small">{{ __('Total Out') }}:</span>
                <strong class="text-danger ms-1">-{{ $currencySymbol }}{{ number_format($totalOut, 2) }}</strong>
            </div>
            <div class="border-start ps-3">
                <span class="text-muted small">{{ __('Net Flow') }}:</span>
                <strong class="{{ $netCash >= 0 ? 'text-primary' : 'text-danger' }} ms-1">
                    {{ $netCash >= 0 ? '+' : '' }}{{ $currencySymbol }}{{ number_format($netCash, 2) }}
                </strong>
            </div>
        </div>
        <div>
            <span class="text-muted small">{{ __('Drawer Balance') }}:</span>
            <strong class="text-dark ms-1">{{ $currentSession ? $currencySymbol . number_format($expectedCash, 2) : '-' }}</strong>
            <span class="badge {{ $currentSession ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-muted' }} ms-1 px-1 py-0" style="font-size: 0.65rem;">
                {{ $currentSession ? __('Open') : __('Closed') }}
            </span>
        </div>
    </div>

    {{-- Single 4-Method Reconciliation Grid --}}
    <div class="row g-2 mb-3">
        @php
            $methodsList = [
                'cash' => [
                    'label' => __('Cash (Drawer)'),
                    'icon' => 'fa-money-bill-wave',
                    'color' => 'success',
                    'badge' => __('Drawer Impact'),
                    'badge_class' => 'bg-success-subtle text-success border-success-subtle',
                ],
                'card' => [
                    'label' => __('Card Terminal'),
                    'icon' => 'fa-credit-card',
                    'color' => 'info',
                    'badge' => __('Terminal Batch'),
                    'badge_class' => 'bg-info-subtle text-info border-info-subtle',
                ],
                'bank_transfer' => [
                    'label' => __('Bank Transfer'),
                    'icon' => 'fa-building-columns',
                    'color' => 'primary',
                    'badge' => __('Direct Bank'),
                    'badge_class' => 'bg-primary-subtle text-primary border-primary-subtle',
                ],
                'other' => [
                    'label' => __('Other Methods'),
                    'icon' => 'fa-ellipsis',
                    'color' => 'secondary',
                    'badge' => __('Vouchers / Custom'),
                    'badge_class' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                ],
            ];
        @endphp
        @foreach ($methodsList as $mKey => $mMeta)
            @php
                $mNet = $methodBreakdown[$mKey]['net'] ?? 0.0;
                $mIn = $methodBreakdown[$mKey]['in'] ?? 0.0;
                $mOut = $methodBreakdown[$mKey]['out'] ?? 0.0;
            @endphp
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-2 bg-white h-100">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge {{ $mMeta['badge_class'] }} border px-1 py-0 fw-bold" style="font-size: 0.72rem;">
                            <i class="fa-solid {{ $mMeta['icon'] }} me-1"></i> {{ $mMeta['label'] }}
                        </span>
                        <span class="fw-bold {{ $mNet >= 0 ? 'text-dark' : 'text-danger' }}" style="font-size: 0.82rem;">
                            {{ $mNet >= 0 ? '+' : '' }}{{ $currencySymbol }}{{ number_format($mNet, 2) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-muted px-1" style="font-size: 0.7rem;">
                        <span>
                            <span class="text-success"><i class="fa-solid fa-arrow-down fa-2xs"></i> +{{ $currencySymbol }}{{ number_format($mIn, 2) }}</span>
                            <span class="mx-1">/</span>
                            <span class="text-danger"><i class="fa-solid fa-arrow-up fa-2xs"></i> -{{ $currencySymbol }}{{ number_format($mOut, 2) }}</span>
                        </span>
                        <span class="text-muted" style="font-size: 0.65rem;">{{ $mMeta['badge'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        {{-- Record Movement Form (Compact Column) --}}
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom border-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark small d-flex align-items-center">
                        <i class="fa-solid fa-money-bill-transfer me-2 text-primary"></i> {{ __('Record Movement') }}
                    </h6>
                    <span class="badge {{ $type === 'in' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-2 py-0" style="font-size: 0.72rem;">
                        {{ $type === 'in' ? __('+ Cash In') : __('- Cash Out') }}
                    </span>
                </div>
                <div class="card-body p-3">
                    {{-- Clean Type Switcher Buttons --}}
                    <div class="btn-group btn-group-sm w-100 mb-2" role="group">
                        <button type="button" wire:click="setType('in')" class="btn py-1 {{ $type === 'in' ? 'btn-success text-white fw-bold shadow-none' : 'btn-outline-secondary' }}">
                            <i class="fa-solid fa-arrow-down me-1"></i> {{ __('+ Cash In (Inflow)') }}
                        </button>
                        <button type="button" wire:click="setType('out')" class="btn py-1 {{ $type === 'out' ? 'btn-danger text-white fw-bold shadow-none' : 'btn-outline-secondary' }}">
                            <i class="fa-solid fa-arrow-up me-1"></i> {{ __('- Cash Out (Outflow)') }}
                        </button>
                    </div>

                    @if (!$currentSession)
                        <div class="alert alert-warning py-1 px-2 mb-2 small" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ __('Open cash register before recording movements.') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="recordMovement">
                        {{-- Payment Method Selector --}}
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-gray-700 mb-0" style="font-size: 0.75rem;">
                                    {{ __('Payment Method') }} <span class="text-danger">*</span>
                                </label>
                                @if ($payment_method === 'cash')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle py-0 px-1" style="font-size: 0.68rem;">
                                        <i class="fa-solid fa-check me-1"></i>{{ __('Hits Drawer') }}
                                    </span>
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle py-0 px-1" style="font-size: 0.68rem;">
                                        <i class="fa-solid fa-info-circle me-1"></i>{{ __('Non-Drawer') }}
                                    </span>
                                @endif
                            </div>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <input type="radio" class="btn-check" name="payment_method" id="pm_cash" value="cash" wire:model.live="payment_method">
                                <label class="btn {{ $payment_method === 'cash' ? 'btn-success text-white fw-bold' : 'btn-outline-secondary' }} py-1 px-1" for="pm_cash" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-money-bill-wave me-1"></i>{{ __('Cash') }}
                                </label>

                                <input type="radio" class="btn-check" name="payment_method" id="pm_card" value="card" wire:model.live="payment_method">
                                <label class="btn {{ $payment_method === 'card' ? 'btn-info text-white fw-bold' : 'btn-outline-secondary' }} py-1 px-1" for="pm_card" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-credit-card me-1"></i>{{ __('Card') }}
                                </label>

                                <input type="radio" class="btn-check" name="payment_method" id="pm_bank" value="bank_transfer" wire:model.live="payment_method">
                                <label class="btn {{ $payment_method === 'bank_transfer' ? 'btn-primary text-white fw-bold' : 'btn-outline-secondary' }} py-1 px-1" for="pm_bank" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-building-columns me-1"></i>{{ __('Bank') }}
                                </label>

                                <input type="radio" class="btn-check" name="payment_method" id="pm_other" value="other" wire:model.live="payment_method">
                                <label class="btn {{ $payment_method === 'other' ? 'btn-secondary text-white fw-bold' : 'btn-outline-secondary' }} py-1 px-1" for="pm_other" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-ellipsis me-1"></i>{{ __('Other') }}
                                </label>
                            </div>
                            @error('payment_method') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Amount & Category Side-by-Side --}}
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fw-bold text-gray-700 mb-1" style="font-size: 0.75rem;">
                                    {{ __('Amount') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-muted fw-bold px-2">{{ $currencySymbol }}</span>
                                    <input wire:model="amount" type="number" step="0.01" min="0.01" class="form-control text-end fw-bold" placeholder="0.00" required>
                                </div>
                                @error('amount') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold text-gray-700 mb-0" style="font-size: 0.75rem;">
                                        {{ __('Category') }} @if ($type === 'in')<span class="text-danger">*</span>@endif
                                    </label>
                                    <button wire:click="openCategoryModal" type="button" class="btn btn-link btn-xs text-primary text-decoration-none p-0 fw-semibold" title="{{ __('Manage Categories') }}">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                                <select wire:model="money_category_id" class="form-select form-select-sm" {{ $type === 'in' ? 'required' : '' }}>
                                    <option value="">{{ $type === 'in' ? __('-- Select --') : __('-- Optional --') }}</option>
                                    @php
                                        $cats = $type === 'in' ? $inCategories : $outCategories;
                                    @endphp
                                    @foreach ($cats as $cat)
                                        <option value="{{ $cat->id }}">{{ __($cat->name) }}</option>
                                    @endforeach
                                </select>
                                @error('money_category_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Reason / Remark --}}
                        <div class="mb-2">
                            <label class="form-label fw-bold text-gray-700 mb-1" style="font-size: 0.75rem;">
                                {{ $type === 'in' ? __('Remark / Explanation') : __('Reason Why Taken') }} <span class="text-danger">*</span>
                            </label>
                            <textarea wire:model="reason" class="form-control form-control-sm" rows="2" placeholder="{{ $type === 'in' ? __('e.g., Owner injection, change float...') : __('e.g., Office supplies, courier...') }}" required></textarea>
                            @error('reason') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Slip Attachment (Compact) --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-gray-700 mb-1" style="font-size: 0.75rem;">
                                {{ __('Receipt / Slip') }} <span class="text-muted fw-normal">({{ __('Optional') }})</span>
                            </label>
                            <input wire:model="attachment" type="file" accept="image/*,application/pdf" class="form-control form-control-sm">
                            @error('attachment') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" @disabled(!$currentSession) class="btn btn-sm w-100 py-2 {{ $type === 'in' ? 'btn-success' : 'btn-danger' }} text-white fw-bold shadow-sm">
                            @if ($type === 'in')
                                <i class="fa-solid fa-circle-check me-1"></i> {{ __('Record Cash In') }}
                            @else
                                <i class="fa-solid fa-circle-minus me-1"></i> {{ __('Record Cash Out') }}
                            @endif
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Money Movements History Table (Expanded Column) --}}
        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom border-light py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold text-dark small d-flex align-items-center">
                            <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> {{ __('Movement History Log') }}
                        </h6>
                        <span class="badge bg-light text-muted border px-2 py-0" style="font-size: 0.72rem;">{{ __('Total') }}: {{ $movements->total() }}</span>
                    </div>
                    <div>
                        <button type="button" wire:click="printReport" wire:loading.attr="disabled" class="btn btn-xs btn-outline-primary d-inline-flex align-items-center shadow-sm" title="{{ __('Print filtered movement history report as PDF') }}">
                            <span wire:loading.remove wire:target="printReport">
                                <i class="fa-solid fa-file-pdf me-1"></i> {{ __('Print PDF') }}
                            </span>
                            <span wire:loading wire:target="printReport">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                {{ __('Generating...') }}
                            </span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    {{-- Compact Filter Controls --}}
                    <div class="p-2 mb-2 bg-light rounded border">
                        <div class="row g-2 align-items-center">
                            <div class="col-6 col-sm-3">
                                <select wire:model.live="filter_type" class="form-select form-select-sm bg-white" style="font-size: 0.75rem;">
                                    <option value="">{{ __('All Types') }}</option>
                                    <option value="in">{{ __('Cash In') }}</option>
                                    <option value="out">{{ __('Cash Out') }}</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-3">
                                <select wire:model.live="filter_payment_method" class="form-select form-select-sm bg-white" style="font-size: 0.75rem;">
                                    <option value="">{{ __('All Methods') }}</option>
                                    <option value="cash">{{ __('Cash (Drawer)') }}</option>
                                    <option value="card">{{ __('Card') }}</option>
                                    <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                            </div>
                            <div class="col-6 col-sm-3">
                                <select wire:model.live="filter_category_id" class="form-select form-select-sm bg-white" style="font-size: 0.75rem;">
                                    <option value="">{{ __('All Categories') }}</option>
                                    @foreach ($allCategories as $c)
                                        <option value="{{ $c->id }}">{{ __($c->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-sm-3">
                                <div class="input-group input-group-sm">
                                    <input wire:model.live="filter_start_date" type="date" class="form-control form-control-sm bg-white px-1" title="{{ __('From Date') }}" style="font-size: 0.72rem;">
                                    <span class="input-group-text bg-white px-1 text-muted border-start-0 border-end-0">-</span>
                                    <input wire:model.live="filter_end_date" type="date" class="form-control form-control-sm bg-white px-1" title="{{ __('To Date') }}" style="font-size: 0.72rem;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.78rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0 px-2 py-1 text-muted">#</th>
                                    <th class="border-0 py-1">{{ __('Type') }}</th>
                                    <th class="border-0 py-1">{{ __('Method') }}</th>
                                    <th class="border-0 py-1 text-end">{{ __('Amount') }}</th>
                                    <th class="border-0 py-1">{{ __('Category') }}</th>
                                    <th class="border-0 py-1">{{ __('Reason / Remark') }}</th>
                                    <th class="border-0 py-1">{{ __('Done By') }}</th>
                                    <th class="border-0 py-1">{{ __('Date') }}</th>
                                    <th class="border-0 py-1 text-center">{{ __('Slip') }}</th>
                                    <th class="border-0 px-2 py-1 text-end">-</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movements as $m)
                                <tr>
                                    <td class="px-2 py-1 text-muted small">{{ $m->id }}</td>
                                    <td class="py-1">
                                        @if ($m->type === 'in')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-1 py-0" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-arrow-down me-1"></i>{{ __('In') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1 py-0" style="font-size: 0.7rem;">
                                                <i class="fa-solid fa-arrow-up me-1"></i>{{ __('Out') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-1">
                                        <span class="badge {{ $m->method_badge_class }} px-1 py-0" style="font-size: 0.7rem;">
                                            <i class="fa-solid {{ $m->method_icon }} me-1"></i>{{ $m->method_label }}
                                        </span>
                                    </td>
                                    <td class="py-1 text-end fw-bold {{ $m->type === 'in' ? 'text-success' : 'text-danger' }}">
                                        {{ $m->type === 'in' ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($m->amount, 2) }}
                                    </td>
                                    <td class="py-1">
                                        <span class="badge bg-light text-dark border px-1 py-0" style="font-size: 0.7rem;">
                                            {{ $m->category ? __($m->category->name) : __('General') }}
                                        </span>
                                    </td>
                                    <td class="py-1" style="max-width: 150px;">
                                        <div class="text-truncate text-dark" title="{{ $m->reason }}">{{ $m->reason }}</div>
                                    </td>
                                    <td class="py-1">
                                        <span class="fw-semibold text-dark small">{{ $m->user?->name ?? '-' }}</span>
                                    </td>
                                    <td class="py-1 text-muted small">
                                        {{ $m->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-1 text-center">
                                        @if ($m->attachment)
                                            <a href="{{ asset($m->attachment) }}" target="_blank" class="btn btn-xs btn-outline-primary px-1 py-0" title="{{ __('View Attachment') }}">
                                                <i class="fa-solid fa-paperclip"></i>
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-1 text-end">
                                        @if (!$m->cash_register_session_id)
                                            <span class="badge bg-warning text-dark d-block mb-1 py-0 px-1" style="font-size: 0.65rem;">{{ __('Outside register') }}</span>
                                            @if ($currentSession && auth()->user()->hasPermissionTo('money-movements:create'))
                                                <button type="button" wire:click="addUnassignedToDrawer({{ $m->id }})" wire:confirm="{{ __('Add this movement to the current drawer? Check that it is not already included in the opening balance.') }}" class="btn btn-xs btn-outline-primary py-0 px-1 mb-1" style="font-size: 0.68rem;">{{ __('Add to drawer') }}</button>
                                            @endif
                                        @endif
                                        @if (auth()->user()->hasRole('admin'))
                                            <button wire:click="deleteMovement({{ $m->id }})" wire:confirm="{{ __('Delete this record?') }}" class="btn btn-xs btn-outline-danger px-1 py-0" title="{{ __('Delete') }}">
                                                <i class="fa-solid fa-trash fa-xs"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fa-solid fa-inbox fs-4 d-block mb-2 text-gray-400"></i>
                                        {{ __('No money movement records found.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2">
                        {{ $movements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Management Modal --}}
    @if ($showCategoryModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-white border-bottom py-3 px-4 rounded-top-3 d-flex justify-content-between align-items-center">
                    <h5 class="modal-title h6 mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="fa-solid fa-tags me-2 text-primary"></i> {{ __('Money Movement Categories') }}
                    </h5>
                    <button wire:click="closeCategoryModal" type="button" class="btn-close" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Modal Alert --}}
                    @if (session()->has('category_success'))
                        <div class="alert alert-success-subtle border border-success-subtle py-2 px-3 mb-3 small text-success-emphasis rounded d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-circle-check me-2"></i>{{ session('category_success') }}</span>
                            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Add / Edit Category Form --}}
                    <div class="border rounded p-3 mb-4 {{ $editingCategoryId ? 'bg-primary-subtle border-primary-subtle' : 'bg-light' }}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                @if ($editingCategoryId)
                                    <i class="fa-solid fa-pen-to-square text-primary me-1"></i> {{ __('Edit Category') }}
                                @else
                                    <i class="fa-solid fa-plus-circle text-primary me-1"></i> {{ __('Add New Category') }}
                                @endif
                            </h6>
                            @if ($editingCategoryId)
                                <button type="button" wire:click="cancelEditCategory" class="btn btn-xs btn-outline-secondary">
                                    <i class="fa-solid fa-times me-1"></i> {{ __('Cancel Edit') }}
                                </button>
                            @endif
                        </div>

                        <form wire:submit.prevent="saveCategory">
                            <div class="row g-2 mb-3">
                                <div class="col-md-5 col-12">
                                    <label class="form-label small fw-bold text-gray-700 mb-1">{{ __('Category Name') }} <span class="text-danger">*</span></label>
                                    <input wire:model="category_name" type="text" class="form-control form-control-sm" placeholder="{{ __('e.g. Partner Investment, Utility...') }}" required>
                                    @error('category_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-3 col-12">
                                    <label class="form-label small fw-bold text-gray-700 mb-1">{{ __('Type') }} <span class="text-danger">*</span></label>
                                    <select wire:model="category_type" class="form-select form-select-sm">
                                        <option value="both">{{ __('Both (In & Out)') }}</option>
                                        <option value="in">{{ __('Cash In Only') }}</option>
                                        <option value="out">{{ __('Cash Out Only') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label small fw-bold text-gray-700 mb-1">{{ __('Description') }}</label>
                                    <input wire:model="category_description" type="text" class="form-control form-control-sm" placeholder="{{ __('Optional notes...') }}">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                @if ($editingCategoryId)
                                    <button type="button" wire:click="cancelEditCategory" class="btn btn-sm btn-outline-secondary px-3">
                                        {{ __('Cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm">
                                        <i class="fa-solid fa-check me-1"></i> {{ __('Update Category') }}
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-primary px-3 shadow-sm">
                                        <i class="fa-solid fa-plus me-1"></i> {{ __('Add Category') }}
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- Existing Categories List --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark">{{ __('Existing Categories') }}</h6>
                        <span class="badge bg-gray-200 text-gray-800">{{ $allCategories->count() }} {{ __('Total') }}</span>
                    </div>

                    <div class="table-responsive border rounded" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0 px-3 py-2">{{ __('Name') }}</th>
                                    <th class="border-0 py-2">{{ __('Type') }}</th>
                                    <th class="border-0 py-2">{{ __('Description') }}</th>
                                    <th class="border-0 px-3 py-2 text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($allCategories as $c)
                                <tr class="{{ $editingCategoryId === $c->id ? 'table-primary' : '' }}">
                                    <td class="px-3 py-2 fw-semibold text-dark">{{ __($c->name) }}</td>
                                    <td class="py-2">
                                        @if ($c->type === 'in')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">{{ __('In') }}</span>
                                        @elseif ($c->type === 'out')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">{{ __('Out') }}</span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">{{ __('Both') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-muted small">{{ $c->description ? __($c->description) : '-' }}</td>
                                    <td class="px-3 py-2 text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button wire:click="editCategory({{ $c->id }})" type="button" class="btn btn-xs {{ $editingCategoryId === $c->id ? 'btn-primary' : 'btn-outline-primary' }} px-2" title="{{ __('Edit Category') }}">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button wire:click="deleteCategory({{ $c->id }})" wire:confirm="{{ __('Delete category :name?', ['name' => $c->name]) }}" type="button" class="btn btn-xs btn-outline-danger px-2" title="{{ __('Delete Category') }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">{{ __('No categories found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 rounded-bottom-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">{{ __('Categories appear in Cash In / Cash Out dropdowns.') }}</small>
                    <button wire:click="closeCategoryModal" type="button" class="btn btn-sm btn-outline-secondary px-3">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
