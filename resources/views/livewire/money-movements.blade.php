<div>
    @include('includes.breadcrumb', ['data' => [__('Money Movements') => '']])

    <div wire:loading.flex wire:target="recordMovement,saveCategory,deleteMovement">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp

    {{-- Page Header --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">{{ __('Money Movements (Cash In / Cash Out)') }}</h1>
            <p class="text-muted small mb-0">{{ __('Record and audit external cash injections, owner withdrawals, and shop expenses.') }}</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0 d-flex align-items-center gap-2">
            <button wire:click="openCategoryModal" type="button" class="btn btn-sm btn-outline-gray-600 d-inline-flex align-items-center">
                <i class="fa-solid fa-tags me-2 text-muted"></i> {{ __('Manage Categories') }}
            </button>
            <a href="{{ route('cashRegister') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center">
                <i class="fa-solid fa-cash-register me-2"></i> {{ __('Cash Register') }}
            </a>
        </div>
    </div>

    {{-- Clean Metric KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">{{ __('Total Cash In (Period)') }}</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                            <i class="fa-solid fa-arrow-down me-1"></i>{{ __('Inflow') }}
                        </span>
                    </div>
                    <h3 class="fs-4 fw-bold text-success mb-1">
                        +{{ $currencySymbol }}{{ number_format($totalIn, 2) }}
                    </h3>
                    <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top">
                        <span>{{ __('Inflow Source') }}:</span>
                        <span class="fw-semibold text-dark">{{ __('Capital & Additions') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">{{ __('Total Cash Out (Period)') }}</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small">
                            <i class="fa-solid fa-arrow-up me-1"></i>{{ __('Outflow') }}
                        </span>
                    </div>
                    <h3 class="fs-4 fw-bold text-danger mb-1">
                        -{{ $currencySymbol }}{{ number_format($totalOut, 2) }}
                    </h3>
                    <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top">
                        <span>{{ __('Outflow Source') }}:</span>
                        <span class="fw-semibold text-dark">{{ __('Expenses & Withdrawals') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold text-uppercase">{{ __('Net Cash Flow (Period)') }}</span>
                        <span class="badge {{ $netCash >= 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-1 small">
                            {{ $netCash >= 0 ? __('Surplus') : __('Deficit') }}
                        </span>
                    </div>
                    <h3 class="fs-4 fw-bold {{ $netCash >= 0 ? 'text-primary' : 'text-danger' }} mb-1">
                        {{ $netCash >= 0 ? '+' : '' }}{{ $currencySymbol }}{{ number_format($netCash, 2) }}
                    </h3>
                    <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top">
                        <span>{{ __('Inflow minus Outflow') }}</span>
                        <span class="text-muted small">{{ __('Filtered Range') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Record Movement Form --}}
        <div class="col-lg-5 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom border-light py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="fa-solid fa-money-bill-transfer me-2 text-primary"></i> {{ __('Record Movement') }}
                    </h6>
                    <span class="badge {{ $type === 'in' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-2 py-1">
                        {{ $type === 'in' ? __('Cash In') : __('Cash Out') }}
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    {{-- Clean Type Switcher Buttons --}}
                    <div class="btn-group w-100 mb-3" role="group">
                        <button type="button" wire:click="setType('in')" class="btn py-2 {{ $type === 'in' ? 'btn-success text-white fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                            <i class="fa-solid fa-arrow-down me-1"></i> {{ __('+ Cash In') }}
                        </button>
                        <button type="button" wire:click="setType('out')" class="btn py-2 {{ $type === 'out' ? 'btn-danger text-white fw-bold shadow-sm' : 'btn-outline-secondary' }}">
                            <i class="fa-solid fa-arrow-up me-1"></i> {{ __('- Cash Out') }}
                        </button>
                    </div>

                    <form wire:submit.prevent="recordMovement">
                        @if ($type === 'in')
                            <div class="alert alert-success-subtle border border-success-subtle py-2 px-3 mb-3 small text-success-emphasis rounded">
                                <i class="fa-solid fa-circle-info me-1"></i> {{ __('Add money to business/drawer. Please select the source category.') }}
                            </div>
                        @else
                            <div class="alert alert-danger-subtle border border-danger-subtle py-2 px-3 mb-3 small text-danger-emphasis rounded">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ __('Take money out for expenses or withdrawals. A clear explanation is required.') }}
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-gray-700 mb-1">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fw-bold">{{ $currencySymbol }}</span>
                                <input wire:model="amount" type="number" step="0.01" min="0.01" class="form-control text-end fw-bold fs-6" placeholder="0.00" required>
                            </div>
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold small text-gray-700 mb-0">
                                    {{ __('Category') }}
                                    @if ($type === 'in')
                                        <span class="text-danger">*</span>
                                    @else
                                        <span class="text-muted fw-normal">({{ __('Optional') }})</span>
                                    @endif
                                </label>
                                <button wire:click="openCategoryModal" type="button" class="btn btn-link btn-xs text-primary text-decoration-none p-0 fw-semibold">
                                    <i class="fa-solid fa-plus me-1"></i>{{ __('New Category') }}
                                </button>
                            </div>
                            <select wire:model="money_category_id" class="form-select" {{ $type === 'in' ? 'required' : '' }}>
                                <option value="">{{ $type === 'in' ? __('-- Select Source Category --') : __('-- Optional Category --') }}</option>
                                @php
                                    $cats = $type === 'in' ? $inCategories : $outCategories;
                                @endphp
                                @foreach ($cats as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('money_category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-gray-700 mb-1">
                                {{ $type === 'in' ? __('Remark / Explanation') : __('Reason Why Money Taken') }}
                                <span class="text-danger">*</span>
                            </label>
                            <textarea wire:model="reason" class="form-control" rows="3" placeholder="{{ $type === 'in' ? __('e.g. Added €200 from personal account for daily change...') : __('e.g. Took €30 for cleaning supplies / petty cash / owner withdrawal...') }}" required></textarea>
                            @error('reason') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-gray-700 mb-1">{{ __('Receipt / Slip Attachment') }} <span class="text-muted fw-normal">({{ __('Optional image/pdf') }})</span></label>
                            <input wire:model="attachment" type="file" accept="image/*,application/pdf" class="form-control">
                            @error('attachment') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="btn w-100 py-2 {{ $type === 'in' ? 'btn-success' : 'btn-danger' }} text-white fw-bold shadow-sm">
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

        {{-- Money Movements History Table --}}
        <div class="col-lg-7 col-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom border-light py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> {{ __('Movement History Log') }}
                    </h6>
                    <span class="text-muted small">{{ __('Total') }}: <strong>{{ $movements->total() }}</strong></span>
                </div>
                <div class="card-body p-3 p-md-4">
                    {{-- Filter Controls --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-3 col-6">
                            <label class="small text-muted mb-1">{{ __('Type') }}</label>
                            <select wire:model.live="filter_type" class="form-select form-select-sm">
                                <option value="">{{ __('All Types') }}</option>
                                <option value="in">{{ __('Cash In') }}</option>
                                <option value="out">{{ __('Cash Out') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="small text-muted mb-1">{{ __('Category') }}</label>
                            <select wire:model.live="filter_category_id" class="form-select form-select-sm">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach ($allCategories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="small text-muted mb-1">{{ __('From Date') }}</label>
                            <input wire:model.live="filter_start_date" type="date" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="small text-muted mb-1">{{ __('To Date') }}</label>
                            <input wire:model.live="filter_end_date" type="date" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-0 px-2 py-2">#</th>
                                    <th class="border-0 py-2">{{ __('Type') }}</th>
                                    <th class="border-0 py-2 text-end">{{ __('Amount') }}</th>
                                    <th class="border-0 py-2">{{ __('Category') }}</th>
                                    <th class="border-0 py-2">{{ __('Reason / Remark') }}</th>
                                    <th class="border-0 py-2">{{ __('Done By') }}</th>
                                    <th class="border-0 py-2">{{ __('Date & Time') }}</th>
                                    <th class="border-0 py-2 text-center">{{ __('Slip') }}</th>
                                    <th class="border-0 px-2 py-2 text-end">-</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movements as $m)
                                <tr>
                                    <td class="px-2 py-2 align-middle text-muted small">{{ $m->id }}</td>
                                    <td class="py-2 align-middle">
                                        @if ($m->type === 'in')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fa-solid fa-arrow-down me-1"></i>{{ __('Cash In') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fa-solid fa-arrow-up me-1"></i>{{ __('Cash Out') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 align-middle text-end fw-bold {{ $m->type === 'in' ? 'text-success' : 'text-danger' }}">
                                        {{ $m->type === 'in' ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($m->amount, 2) }}
                                    </td>
                                    <td class="py-2 align-middle">
                                        <span class="badge bg-gray-100 text-gray-800 border px-2 py-1">{{ $m->category?->name ?? __('General') }}</span>
                                    </td>
                                    <td class="py-2 align-middle" style="max-width: 200px; white-space: normal; line-height: 1.35;">
                                        <small class="text-dark">{{ $m->reason }}</small>
                                    </td>
                                    <td class="py-2 align-middle">
                                        <span class="fw-semibold text-dark small">{{ $m->user?->name ?? '-' }}</span>
                                    </td>
                                    <td class="py-2 align-middle text-muted small">
                                        {{ $m->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-2 align-middle text-center">
                                        @if ($m->attachment)
                                            <a href="{{ asset($m->attachment) }}" target="_blank" class="btn btn-xs btn-outline-primary px-2" title="{{ __('View Attachment') }}">
                                                <i class="fa-solid fa-paperclip"></i>
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 align-middle text-end">
                                        @if (auth()->user()->hasRole('admin'))
                                            <button wire:click="deleteMovement({{ $m->id }})" wire:confirm="{{ __('Delete this record?') }}" class="btn btn-xs btn-outline-danger px-2" title="{{ __('Delete') }}">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-inbox fs-4 d-block mb-2 text-gray-400"></i>
                                        {{ __('No money movement records found.') }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
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
                <div class="modal-header bg-gray-800 text-white py-3 px-4 rounded-top-3">
                    <h5 class="modal-title h6 mb-0 text-white">
                        <i class="fa-solid fa-tags me-2 text-warning"></i> {{ __('Money Movement Categories') }}
                    </h5>
                    <button wire:click="closeCategoryModal" type="button" class="btn-close btn-close-white"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Add New Category Form --}}
                    <div class="border rounded p-3 bg-light mb-4">
                        <h6 class="fw-bold mb-3">{{ __('Add New Source / Expense Category') }}</h6>
                        <form wire:submit.prevent="saveCategory">
                            <div class="row g-2 mb-2">
                                <div class="col-md-5 col-12">
                                    <label class="small fw-bold mb-1">{{ __('Category Name') }} <span class="text-danger">*</span></label>
                                    <input wire:model="category_name" type="text" class="form-control form-control-sm" placeholder="{{ __('e.g. Partner Investment, Utility...') }}" required>
                                    @error('category_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-3 col-12">
                                    <label class="small fw-bold mb-1">{{ __('Type') }} <span class="text-danger">*</span></label>
                                    <select wire:model="category_type" class="form-select form-select-sm">
                                        <option value="both">{{ __('Both (In & Out)') }}</option>
                                        <option value="in">{{ __('Cash In Only') }}</option>
                                        <option value="out">{{ __('Cash Out Only') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="small fw-bold mb-1">{{ __('Description') }}</label>
                                    <input wire:model="category_description" type="text" class="form-control form-control-sm" placeholder="{{ __('Optional notes...') }}">
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-sm btn-primary px-3">
                                    <i class="fa-solid fa-plus me-1"></i> {{ __('Add Category') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Existing Categories List --}}
                    <h6 class="fw-bold mb-2">{{ __('Existing Categories') }}</h6>
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allCategories as $c)
                                <tr>
                                    <td class="fw-bold">{{ $c->name }}</td>
                                    <td>
                                        @if ($c->type === 'in')
                                            <span class="badge bg-success">{{ __('In') }}</span>
                                        @elseif ($c->type === 'out')
                                            <span class="badge bg-danger">{{ __('Out') }}</span>
                                        @else
                                            <span class="badge bg-info text-white">{{ __('Both') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $c->description ?: '-' }}</td>
                                    <td class="text-end">
                                        <button wire:click="deleteCategory({{ $c->id }})" wire:confirm="{{ __('Delete category?') }}" class="btn btn-xs btn-link text-danger p-0">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 rounded-bottom-3">
                    <button wire:click="closeCategoryModal" type="button" class="btn btn-outline-secondary">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
