<div>
    @include('includes.breadcrumb', ['data' => [__('Money Movements (Cash In / Cash Out)') => '']])

    <div wire:loading.flex wire:target="recordMovement,saveCategory,deleteMovement">
        @include('includes.spinner')
    </div>

    @include('includes.alert')

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body p-3">
                    <small class="text-white-50 text-uppercase fw-bold">{{ __('Total Cash In (Period)') }}</small>
                    <h3 class="fs-4 fw-bold mb-0 mt-1">+{{ $currencySymbol }}{{ number_format($totalIn, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body p-3">
                    <small class="text-white-50 text-uppercase fw-bold">{{ __('Total Cash Out (Period)') }}</small>
                    <h3 class="fs-4 fw-bold mb-0 mt-1">-{{ $currencySymbol }}{{ number_format($totalOut, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body p-3">
                    <small class="text-white-50 text-uppercase fw-bold">{{ __('Net Cash Flow (Period)') }}</small>
                    <h3 class="fs-4 fw-bold mb-0 mt-1">
                        {{ $netCash >= 0 ? '+' : '' }}{{ $currencySymbol }}{{ number_format($netCash, 2) }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        {{-- Record Movement Form --}}
        <div class="col-lg-5 col-12 mb-4 mb-lg-0">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-gray-800 text-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="h6 mb-0 text-white d-flex align-items-center">
                        <i class="fa-solid fa-money-bill-transfer me-2 text-primary"></i> {{ __('Record Money Movement') }}
                    </h5>
                    <button wire:click="openCategoryModal" type="button" class="btn btn-xs btn-outline-light">
                        <i class="fa-solid fa-tags me-1"></i> {{ __('Manage Categories') }}
                    </button>
                </div>
                <div class="card-body p-4">
                    {{-- Type Selector Tabs --}}
                    <div class="d-flex mb-3 rounded p-1 bg-light border">
                        <button type="button" wire:click="setType('in')" class="btn w-50 py-2 fw-bold {{ $type === 'in' ? 'btn-success text-white shadow-sm' : 'btn-link text-muted text-decoration-none' }}">
                            <i class="fa-solid fa-arrow-down me-1"></i> {{ __('+ Cash In (Add Money)') }}
                        </button>
                        <button type="button" wire:click="setType('out')" class="btn w-50 py-2 fw-bold {{ $type === 'out' ? 'btn-danger text-white shadow-sm' : 'btn-link text-muted text-decoration-none' }}">
                            <i class="fa-solid fa-arrow-up me-1"></i> {{ __('- Cash Out (Take Money)') }}
                        </button>
                    </div>

                    <form wire:submit.prevent="recordMovement">
                        @if ($type === 'in')
                            <div class="alert alert-success py-2 mb-3 small">
                                <i class="fa-solid fa-info-circle me-1"></i> {{ __('Adding money to business/cash register. Select the category source below.') }}
                            </div>
                        @else
                            <div class="alert alert-danger py-2 mb-3 small">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> {{ __('Taking money out from business/cash drawer. You must state the clear reason why.') }}
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currencySymbol }}</span>
                                <input wire:model="amount" type="number" step="0.01" min="0.01" class="form-control form-control-lg text-end fw-bold" placeholder="0.00" required>
                            </div>
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">
                                {{ __('Category') }}
                                @if ($type === 'in')
                                    <span class="text-danger">*</span>
                                @else
                                    <small class="text-muted fw-normal">({{ __('Optional') }})</small>
                                @endif
                            </label>
                            <div class="input-group">
                                <select wire:model="money_category_id" class="form-select" {{ $type === 'in' ? 'required' : '' }}>
                                    <option value="">{{ $type === 'in' ? __('-- Select Source Category --') : __('-- Optional Category --') }}</option>
                                    @php
                                        $cats = $type === 'in' ? $inCategories : $outCategories;
                                    @endphp
                                    @foreach ($cats as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <button wire:click="openCategoryModal" type="button" class="btn btn-outline-secondary" title="{{ __('Add New Category') }}">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                            @error('money_category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">
                                {{ $type === 'in' ? __('Remark / Explanation') : __('Reason Why Money Taken') }}
                                <span class="text-danger">*</span>
                            </label>
                            <textarea wire:model="reason" class="form-control" rows="3" placeholder="{{ $type === 'in' ? __('e.g. Added €200 from personal account for daily change...') : __('e.g. Took €30 for cleaning supplies / petty cash / owner withdrawal...') }}" required></textarea>
                            @error('reason') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small mb-1">{{ __('Receipt / Slip Attachment') }} <small class="text-muted fw-normal">({{ __('Optional image/pdf') }})</small></label>
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
            <div class="card border-0 shadow">
                <div class="card-header bg-gray-800 text-white py-3">
                    <h5 class="h6 mb-0 text-white d-flex align-items-center">
                        <i class="fa-solid fa-list-check me-2"></i> {{ __('Money Movement Log') }}
                    </h5>
                </div>
                <div class="card-body">
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
                        <table class="table table-centered table-nowrap mb-0 rounded">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Reason / Remark') }}</th>
                                    <th>{{ __('Done By') }}</th>
                                    <th>{{ __('Date & Time') }}</th>
                                    <th>{{ __('Slip') }}</th>
                                    <th class="text-end">-</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movements as $m)
                                <tr>
                                    <td>{{ $m->id }}</td>
                                    <td>
                                        @if ($m->type === 'in')
                                            <span class="badge bg-success"><i class="fa-solid fa-arrow-down me-1"></i> {{ __('Cash In') }}</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fa-solid fa-arrow-up me-1"></i> {{ __('Cash Out') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $m->type === 'in' ? 'text-success' : 'text-danger' }}">
                                        {{ $m->type === 'in' ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($m->amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $m->category?->name ?? 'General' }}</span>
                                    </td>
                                    <td style="max-width: 180px; white-space: normal;">
                                        <small>{{ $m->reason }}</small>
                                    </td>
                                    <td>
                                        <b>{{ $m->user?->name ?? '-' }}</b>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $m->created_at->format('Y-m-d H:i') }}</small>
                                    </td>
                                    <td>
                                        @if ($m->attachment)
                                            <a href="{{ asset($m->attachment) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="{{ __('View Attachment') }}">
                                                <i class="fa-solid fa-paperclip"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if (auth()->user()->hasRole('admin'))
                                            <button wire:click="deleteMovement({{ $m->id }})" wire:confirm="{{ __('Delete this record?') }}" class="btn btn-xs btn-link text-danger p-0">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">{{ __('No money movement records found.') }}</td>
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
