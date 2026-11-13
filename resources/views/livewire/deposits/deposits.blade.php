<div>
    @include('includes.breadcrumb', ['data' => [__('Deposits / Advance Receipts') => route('deposits.index')]])

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp

    {{-- ── DEPOSIT SUCCESS / PRINT LANGUAGE OVERLAY ──────────────────── --}}
    @if ($showSuccessModal)
    <div
        style="position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:99990;display:flex;align-items:center;justify-content:center;"
        wire:key="deposit-success-overlay"
    >
        <div class="card border-0 shadow-lg text-center" style="min-width:340px;max-width:440px;border-radius:1rem;overflow:hidden;">

            {{-- Green header bar --}}
            <div class="bg-success text-white px-4 pt-4 pb-3">
                <div class="mb-2" style="font-size:3rem;line-height:1;">&#10004;</div>
                <h5 class="fw-bold mb-1">{{ __('Deposit Receipt Created!') }}</h5>
                <div class="opacity-75 small">#{{ $lastReferenceNo }} &bull; {{ $lastCustomerName }}</div>
            </div>

            {{-- Body --}}
            <div class="card-body px-4 py-3">
                {{-- Financial summary --}}
                <div class="p-2 bg-light rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small">{{ __('Deposit Paid') }}</span>
                        <span class="fw-bold text-success fs-5">
                            {{ $currencySymbol }}{{ $lastDepositAmount }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">{{ __('Remaining Balance') }}</span>
                        <span class="fw-bold text-danger">
                            {{ $currencySymbol }}{{ $lastRemainingBalance }}
                        </span>
                    </div>
                </div>

                <div class="fw-bold text-secondary small text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.72rem;">
                    {{ __('Choose Receipt Language to Print') }}
                </div>

                <div class="d-grid gap-2">
                    {{-- English / French thermal POS receipt choices --}}
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-dark w-100 fw-bold py-3"
                                wire:click="printDepositReceiptPos({{ $lastDepositId }}, 'en')"
                                wire:loading.attr="disabled" wire:target="printDepositReceiptPos">
                                <span wire:loading.remove wire:target="printDepositReceiptPos"><i class="fa-solid fa-print me-1"></i>{{ __('English receipt') }}</span>
                                <span wire:loading wire:target="printDepositReceiptPos"><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Printing…') }}</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-dark w-100 fw-bold py-3"
                                wire:click="printDepositReceiptPos({{ $lastDepositId }}, 'fr')"
                                wire:loading.attr="disabled" wire:target="printDepositReceiptPos">
                                <span wire:loading.remove wire:target="printDepositReceiptPos"><i class="fa-solid fa-print me-1"></i>{{ __('French receipt') }}</span>
                                <span wire:loading wire:target="printDepositReceiptPos"><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Printing…') }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- PDF 80mm & A4 Invoices (EN/FR) --}}
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-sm btn-outline-danger w-50 py-1"
                                    wire:click="printDepositReceipt({{ $lastDepositId }}, 'en')"
                                    title="{{ __('English 80mm PDF') }}">
                                    <i class="fa-solid fa-file-pdf me-1"></i>PDF EN
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger w-50 py-1"
                                    wire:click="printDepositReceipt({{ $lastDepositId }}, 'fr')"
                                    title="{{ __('French 80mm PDF') }}">
                                    <i class="fa-solid fa-file-pdf me-1"></i>PDF FR
                                </button>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-sm btn-outline-info w-50 py-1"
                                    wire:click="printDepositReceiptA4({{ $lastDepositId }}, 'en')"
                                    title="{{ __('English A4 PDF') }}">
                                    <i class="fa-solid fa-file-lines me-1"></i>A4 EN
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info w-50 py-1"
                                    wire:click="printDepositReceiptA4({{ $lastDepositId }}, 'fr')"
                                    title="{{ __('French A4 PDF') }}">
                                    <i class="fa-solid fa-file-lines me-1"></i>A4 FR
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Close button --}}
                    <button
                        type="button"
                        class="btn btn-outline-secondary fw-semibold mt-2"
                        wire:click="dismissDepositSuccess"
                    >
                        <i class="fa-solid fa-xmark me-1"></i>{{ __('Close') }}
                    </button>
                </div>
            </div>

        </div>
    </div>
    @endif

    <div wire:loading.flex wire:target="saveDeposit,markCompleted,deleteDeposit">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    {{-- Top Summary KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape icon-md bg-warning text-white rounded me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">{{ __('Active Deposits') }}</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ $totalPendingCount }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape icon-md bg-success text-white rounded me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-hand-holding-dollar fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">{{ __('Advance Collected') }}</span>
                        <h4 class="fw-bold mb-0 text-success">{{ $currencySymbol }}{{ number_format($totalDepositCollected, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape icon-md bg-danger text-white rounded me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-receipt fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">{{ __('Remaining Due') }}</span>
                        <h4 class="fw-bold mb-0 text-danger">{{ $currencySymbol }}{{ number_format($totalRemainingDue, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape icon-md bg-primary text-white rounded me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">{{ __('Completed / Settled') }}</span>
                        <h4 class="fw-bold mb-0 text-primary">{{ $totalCompletedCount }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card & New Button --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="h6 mb-0 fw-bold">
                    <i class="fa-solid fa-filter me-1 text-primary"></i> {{ __('Filter & Search Deposits') }}
                </h5>
                <button type="button" wire:click="openCreateModal" class="btn btn-sm btn-primary px-3 shadow-sm">
                    <i class="fa-solid fa-plus me-1"></i> {{ __('New Deposit Receipt') }}
                </button>
            </div>

            <div class="row g-2">
                <div class="col-md-4 col-12">
                    <label class="small text-muted mb-1">{{ __('Search') }}</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="{{ __('Search Ref #, Customer Name, Phone or Item...') }}">
                    </div>
                </div>

                <div class="col-md-2 col-6">
                    <label class="small text-muted mb-1">{{ __('Status') }}</label>
                    <select wire:model.live="status_filter" class="form-select form-select-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending">{{ __('Pending / Active') }}</option>
                        <option value="completed">{{ __('Completed / Settled') }}</option>
                        <option value="cancelled">{{ __('Cancelled') }}</option>
                    </select>
                </div>

                <div class="col-md-3 col-6">
                    <label class="small text-muted mb-1">{{ __('From Date') }}</label>
                    <input wire:model.live="date_from" type="date" class="form-control form-control-sm">
                </div>

                <div class="col-md-3 col-6">
                    <label class="small text-muted mb-1">{{ __('To Date') }}</label>
                    <input wire:model.live="date_to" type="date" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>

    {{-- Deposits Records Table --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="h6 mb-0 fw-bold">{{ __('Deposit Records') }}</h5>
                <span class="small text-muted">{{ $deposits->total() }} {{ __('record(s) found') }}</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-centered table-nowrap mb-0 rounded align-middle">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0 rounded-start" style="width: 2%;">#</th>
                            <th class="border-0">{{ __('Reference #') }}</th>
                            <th class="border-0">{{ __('Date & Time') }}</th>
                            <th class="border-0">{{ __('Customer') }}</th>
                            <th class="border-0" style="width: 28%;">{{ __('Product / Problem Description') }}</th>
                            <th class="border-0 text-end">{{ __('Total Amount') }}</th>
                            <th class="border-0 text-end">{{ __('Deposit Paid') }}</th>
                            <th class="border-0 text-end">{{ __('Remaining Due') }}</th>
                            <th class="border-0 text-center">{{ __('Payment') }}</th>
                            <th class="border-0 text-center">{{ __('Status') }}</th>
                            <th class="border-0 text-center">{{ __('Staff') }}</th>
                            <th class="border-0 text-end rounded-end" style="width: 5%;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deposits as $deposit)
                            <tr wire:key="deposit_row_{{ $deposit->id }}">
                                <td>{{ $deposits->firstItem() + $loop->index }}</td>

                                <td class="fw-bold font-monospace text-primary">
                                    {{ $deposit->reference_no }}
                                </td>

                                <td class="lh-1">
                                    <small class="fw-bold d-block">{{ $deposit->created_at->format('Y-m-d') }}</small>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $deposit->created_at->format('h:i A') }}</small>
                                </td>

                                <td class="lh-1">
                                    <span class="fw-bold text-dark d-block">{{ $deposit->customer_name }}</span>
                                    @if ($deposit->customer_phone)
                                        <small class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $deposit->customer_phone }}</small>
                                    @endif
                                </td>

                                <td>
                                    <div class="text-wrap" style="max-width: 280px; font-size: 0.82rem;">
                                        {{ $deposit->item_description }}
                                    </div>
                                    @if ($deposit->notes)
                                        <small class="text-muted d-block font-italic mt-1" style="font-size: 0.7rem;">
                                            <i class="fa-solid fa-note-sticky me-1"></i>{{ $deposit->notes }}
                                        </small>
                                    @endif
                                </td>

                                <td class="text-end font-monospace fw-semibold">
                                    {{ $currencySymbol }}{{ number_format($deposit->total_amount, 2) }}
                                </td>

                                <td class="text-end font-monospace fw-bold text-success">
                                    {{ $currencySymbol }}{{ number_format($deposit->deposit_amount, 2) }}
                                </td>

                                <td class="text-end font-monospace fw-bold {{ (float) $deposit->remaining_balance > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ $currencySymbol }}{{ number_format($deposit->remaining_balance, 2) }}
                                </td>

                                <td class="text-center">
                                    @php
                                        $pBadge = match(strtolower($deposit->payment_method ?: 'cash')) {
                                            'cash' => 'bg-success',
                                            'card' => 'bg-info text-white',
                                            'bank_transfer' => 'bg-warning text-dark',
                                            default => 'bg-secondary',
                                        };
                                        $pMethodLabel = match(strtolower($deposit->payment_method ?: 'cash')) {
                                            'cash', 'espece', 'espèces', 'especes' => __('Cash'),
                                            'card', 'carte', 'cb', 'credit_card', 'carte bancaire' => __('Card'),
                                            'bank_transfer', 'virement', 'transfer' => __('Bank transfer'),
                                            'other', 'autre' => __('Other'),
                                            default => ucfirst(str_replace('_', ' ', $deposit->payment_method)),
                                        };
                                    @endphp
                                    <span class="badge {{ $pBadge }} text-uppercase" style="font-size: 0.68rem;">
                                        {{ $pMethodLabel }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if ($deposit->status === 'completed')
                                        <span class="badge bg-primary text-white" style="font-size: 0.68rem;">
                                            <i class="fa-solid fa-check me-1"></i>{{ __('Completed') }}
                                        </span>
                                    @elseif ($deposit->status === 'cancelled')
                                        <span class="badge bg-secondary" style="font-size: 0.68rem;">
                                            {{ __('Cancelled') }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark" style="font-size: 0.68rem;">
                                            <i class="fa-solid fa-hourglass-half me-1"></i>{{ __('Pending') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center small text-muted">
                                    {{ $deposit->createdBy?->name ?? '-' }}
                                </td>

                                <td class="text-end align-middle">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary btn-sm dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow py-2" style="min-width: 17.5rem;">
                                            <li>
                                                <a class="dropdown-item text-primary py-2" href="#" wire:click.prevent="openEditModal({{ $deposit->id }})">
                                                    <i class="fa-solid fa-pen-to-square pe-2"></i>{{ __('Edit Details') }}
                                                </a>
                                            </li>

                                            @if ($deposit->status === 'pending')
                                                <li>
                                                    <a class="dropdown-item text-success py-2" href="#" wire:click.prevent="markCompleted({{ $deposit->id }})" wire:confirm="{{ __('Mark this deposit as completed and fully settled?') }}">
                                                        <i class="fa-solid fa-circle-check pe-2"></i>{{ __('Mark as Settled / Picked Up') }}
                                                    </a>
                                                </li>
                                            @endif

                                            <li><hr class="dropdown-divider my-2"></li>

                                            <li class="dropdown-header text-uppercase text-muted fw-bold px-3 py-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                                <i class="fa-solid fa-print me-1"></i>{{ __('Print / Download') }}
                                            </li>

                                            {{-- POS (80mm) --}}
                                            <li class="px-3 py-1">
                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                    <span class="small fw-semibold text-secondary text-nowrap">
                                                        <i class="fa-solid fa-print me-2 text-dark"></i>{{ __('POS (80mm)') }}
                                                    </span>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-dark fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                            wire:click="printDepositReceiptPos({{ $deposit->id }}, 'en')"
                                                            title="{{ __('English POS receipt') }}">
                                                            EN
                                                        </button>
                                                        <button type="button" class="btn btn-outline-dark fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                            wire:click="printDepositReceiptPos({{ $deposit->id }}, 'fr')"
                                                            title="{{ __('French POS receipt') }}">
                                                            FR
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>

                                            {{-- PDF (80mm) --}}
                                            <li class="px-3 py-1">
                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                    <span class="small fw-semibold text-secondary text-nowrap">
                                                        <i class="fa-solid fa-file-pdf me-2 text-danger"></i>{{ __('Receipt (PDF)') }}
                                                    </span>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-danger fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                            wire:click="printDepositReceipt({{ $deposit->id }}, 'en')"
                                                            title="{{ __('English 80mm PDF') }}">
                                                            EN
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                            wire:click="printDepositReceipt({{ $deposit->id }}, 'fr')"
                                                            title="{{ __('French 80mm PDF') }}">
                                                            FR
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>

                                            {{-- Invoice (A4) --}}
                                            <li class="px-3 py-1">
                                                <div class="d-flex align-items-center justify-content-between gap-2">
                                                    <span class="small fw-semibold text-secondary text-nowrap">
                                                        <i class="fa-solid fa-file-lines me-2 text-info"></i>{{ __('Invoice (A4)') }}
                                                    </span>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-info fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                            wire:click="printDepositReceiptA4({{ $deposit->id }}, 'en')"
                                                            title="{{ __('English A4 PDF') }}">
                                                            EN
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info fw-bold px-2 py-0" style="font-size: 0.72rem; min-height: 24px;"
                                                            wire:click="printDepositReceiptA4({{ $deposit->id }}, 'fr')"
                                                            title="{{ __('French A4 PDF') }}">
                                                            FR
                                                        </button>
                                                    </div>
                                                </div>
                                            </li>

                                            <li><hr class="dropdown-divider my-2"></li>
                                            <li>
                                                <a class="dropdown-item text-danger py-2" href="#" wire:click.prevent="deleteDeposit({{ $deposit->id }})" wire:confirm="{{ __('Are you sure you want to delete this deposit record?') }}">
                                                    <i class="fa-solid fa-trash pe-2"></i>{{ __('Delete') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-5">
                                    <i class="fa-solid fa-receipt fs-2 mb-2 d-block text-secondary"></i>
                                    {{ __('No deposit records found matching the criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $deposits->links() }}
            </div>
        </div>
    </div>

    {{-- Create / Edit Deposit Modal --}}
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.65);" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title h6 fw-bold mb-0">
                            <i class="fa-solid fa-receipt me-2"></i>
                            {{ $editId ? __('Edit Deposit Receipt') : __('Create Deposit / Advance Payment Receipt') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal" aria-label="Close"></button>
                    </div>

                    <form wire:submit.prevent="saveDeposit">
                        <div class="modal-body p-4">
                            {{-- Customer Details --}}
                            <div class="card border border-light-subtle bg-light p-3 rounded mb-3">
                                <h6 class="fw-bold mb-2 text-dark small text-uppercase">
                                    <i class="fa-solid fa-user me-1 text-primary"></i> {{ __('Customer Information') }}
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-5 col-12 position-relative">
                                        <label class="small fw-semibold mb-1">{{ __('Customer Name') }} <span class="text-danger">*</span></label>
                                        <input wire:model.live="customer_name" type="text" class="form-control form-control-sm @error('customer_name') is-invalid @enderror" autocomplete="off">
                                        @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                        {{-- Autocomplete Suggestions --}}
                                        @if ($show_customer_suggestions && !empty($customer_suggestions))
                                            <ul class="list-group position-absolute w-100 shadow-lg z-3 mt-1" style="max-height: 180px; overflow-y: auto;">
                                                @foreach ($customer_suggestions as $cust)
                                                    <li class="list-group-item list-group-item-action py-1 px-2 cursor-pointer small" wire:click="selectCustomer({{ $cust['id'] }})" style="cursor: pointer;">
                                                        <strong>{{ $cust['name'] }}</strong>
                                                        @if (!empty($cust['phone_no']))
                                                            <small class="text-muted font-monospace ms-1">({{ $cust['phone_no'] }})</small>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>

                                    <div class="col-md-4 col-6">
                                        <label class="small fw-semibold mb-1">{{ __('Phone Number') }}</label>
                                        <input wire:model="customer_phone" type="text" class="form-control form-control-sm">
                                    </div>

                                    <div class="col-md-3 col-6">
                                        <label class="small fw-semibold mb-1">{{ __('Email') }}</label>
                                        <input wire:model="customer_email" type="email" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>

                            {{-- Product / Problem Description --}}
                            <div class="mb-3">
                                <label class="small fw-semibold mb-1">
                                    {{ __('Product / Problem Description') }} <span class="text-danger">*</span>
                                </label>
                                <textarea wire:model="item_description" class="form-control form-control-sm @error('item_description') is-invalid @enderror" rows="3"></textarea>
                                @error('item_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Financial Breakdown --}}
                            <div class="card border border-light-subtle bg-light p-3 rounded mb-3">
                                <h6 class="fw-bold mb-2 text-dark small text-uppercase">
                                    <i class="fa-solid fa-calculator me-1 text-primary"></i> {{ __('Financial Breakdown') }}
                                </h6>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4 col-12">
                                        <label class="small fw-semibold mb-1">{{ __('Total Agreed Amount') }} <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">{{ $currencySymbol }}</span>
                                            <input wire:model.live="total_amount" type="number" step="0.01" min="0" class="form-control text-end fw-bold font-monospace @error('total_amount') is-invalid @enderror" placeholder="0.00">
                                        </div>
                                        @error('total_amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 col-12">
                                        <label class="small fw-semibold mb-1 text-success">{{ __('Deposit Amount Paid') }} <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-success-subtle text-success">{{ $currencySymbol }}</span>
                                            <input wire:model.live="deposit_amount" type="number" step="0.01" min="0" class="form-control text-end fw-bold text-success font-monospace @error('deposit_amount') is-invalid @enderror" placeholder="0.00">
                                        </div>
                                        @error('deposit_amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 col-12">
                                        <label class="small fw-semibold mb-1 text-danger">{{ __('Remaining Balance Due') }}</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-danger-subtle text-danger">{{ $currencySymbol }}</span>
                                            <input type="text" readonly class="form-control text-end fw-bold text-danger font-monospace bg-white border-danger" value="{{ number_format((float) $remaining_balance, 2) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Details & Status --}}
                            <div class="row g-3 mb-2">
                                <div class="col-md-4 col-6">
                                    <label class="small fw-semibold mb-1">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                                    <select wire:model="payment_method" class="form-select form-select-sm">
                                        <option value="cash">{{ __('Cash') }}</option>
                                        <option value="card">{{ __('Card') }}</option>
                                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                                        <option value="other">{{ __('Other') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-4 col-6">
                                    <label class="small fw-semibold mb-1">{{ __('Payment Reference') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                    <input wire:model="payment_reference" type="text" class="form-control form-control-sm">
                                </div>

                                <div class="col-md-4 col-12">
                                    <label class="small fw-semibold mb-1">{{ __('Status') }}</label>
                                    <select wire:model="status" class="form-select form-select-sm">
                                        <option value="pending">{{ __('Pending / Active') }}</option>
                                        <option value="completed">{{ __('Completed / Settled') }}</option>
                                        <option value="cancelled">{{ __('Cancelled') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-1">
                                <label class="small fw-semibold mb-1">{{ __('Internal Notes') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                <input wire:model="notes" type="text" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="modal-footer bg-light py-2 px-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" wire:click="closeModal">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold shadow-sm">
                                <i class="fa-solid fa-check me-1"></i> {{ $editId ? __('Update Receipt') : __('Save & Issue Receipt') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
