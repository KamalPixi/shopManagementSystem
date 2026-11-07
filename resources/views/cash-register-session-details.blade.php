@extends('layouts.default')

@section('content')
<div class="container-fluid py-4">
    @include('includes.breadcrumb', ['data' => [__('Cash Register / Drawer') => route('cashRegister'), __('Session Details') => '']])
    @php
        $currency = \App\Models\Shop::first()?->currency['symbol'] ?? '€';
        $breakdown = $session->calculatePaymentBreakdown();
        $cashSales = $breakdown['cash'];
        $cashIn = $session->moneyMovements->where('type', 'in')->sum('amount');
        $cashOut = $session->moneyMovements->where('type', 'out')->sum('amount');
        $expected = (float) $session->opening_balance + $cashSales + $cashIn - $cashOut;
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">{{ __('Cash Register Session Details') }} #{{ $session->id }}</h1>
        <a href="{{ route('cashRegister') }}" class="btn btn-outline-secondary">{{ __('Back to Cash Register') }}</a>
    </div>

    {{-- Drawer Balances --}}
    <div class="row g-3 mb-3">
        @foreach ([__('Opening Balance') => $session->opening_balance, __('Cash Sales (Drawer)') => $cashSales, __('Cash In') => $cashIn, __('Cash Out') => $cashOut, __('Expected Closing Cash') => $expected] as $label => $amount)
            <div class="col-md col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted fw-bold">{{ $label }}</small><div class="fs-5 fw-bold">{{ $currency }}{{ number_format($amount, 2) }}</div></div></div></div>
        @endforeach
    </div>

    {{-- Non-Cash & Total Revenue Breakdown --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-2">
            <span><i class="fa-solid fa-credit-card me-1 text-primary"></i> {{ __('Payment Methods & Shift Totals') }}</span>
            <span class="small text-muted">{{ __('Total Shift Sales') }}: <strong class="text-dark">{{ $currency }}{{ number_format($breakdown['total_sales'], 2) }}</strong></span>
        </div>
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-3 col-6">
                    <div class="border rounded p-2 bg-light text-center">
                        <small class="text-muted fw-semibold d-block" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-money-bill-wave text-success me-1"></i> {{ __('Cash Total') }}
                        </small>
                        <span class="fs-6 fw-bold text-success">{{ $currency }}{{ number_format($breakdown['cash'], 2) }}</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded p-2 bg-light text-center">
                        <small class="text-muted fw-semibold d-block" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-credit-card text-info me-1"></i> {{ __('Card Terminal Total') }}
                        </small>
                        <span class="fs-6 fw-bold text-info">{{ $currency }}{{ number_format($breakdown['card'], 2) }}</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded p-2 bg-light text-center">
                        <small class="text-muted fw-semibold d-block" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-building-columns text-primary me-1"></i> {{ __('Bank Transfer Total') }}
                        </small>
                        <span class="fs-6 fw-bold text-primary">{{ $currency }}{{ number_format($breakdown['bank_transfer'], 2) }}</span>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="border rounded p-2 bg-light text-center">
                        <small class="text-muted fw-semibold d-block" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-ellipsis text-secondary me-1"></i> {{ __('Other Payments') }}
                        </small>
                        <span class="fs-6 fw-bold text-secondary">{{ $currency }}{{ number_format($breakdown['other'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">{{ __('Session Entries') }}</div>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th>{{ __('Date & Time') }}</th><th>{{ __('Type') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Category') }}</th><th>{{ __('Reason / Remark') }}</th><th>{{ __('Done By') }}</th></tr></thead><tbody>
            @forelse ($session->moneyMovements->sortBy('created_at') as $movement)
                <tr><td>{{ $movement->created_at->format('d/m/Y H:i') }}</td><td>{{ $movement->type === 'in' ? __('Cash In') : __('Cash Out') }}</td><td class="fw-bold {{ $movement->type === 'in' ? 'text-success' : 'text-danger' }}">{{ $movement->type === 'in' ? '+' : '-' }}{{ $currency }}{{ number_format($movement->amount, 2) }}</td><td>{{ $movement->category?->name ?? '-' }}</td><td>{{ $movement->reason }}</td><td>{{ $movement->user?->name ?? '-' }}</td></tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No money movements recorded.') }}</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center"><span>{{ __('Session Sales & Payments') }}</span> <span class="small text-muted">{{ $session->sales->count() }} {{ __('sales') }}</span></div><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th>{{ __('Date & Time') }}</th><th>{{ __('Receipt / Sale') }}</th><th>{{ __('Payment Breakdown') }}</th><th class="text-end">{{ __('Total Amount') }}</th><th>{{ __('Created By') }}</th></tr></thead><tbody>
        @forelse ($session->sales->sortBy('created_at') as $sale)
            <tr>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td><b>{{ $sale->receipt_no ?? sprintf('TP-%06d', $sale->id) }}</b></td>
                <td>
                    @if ($sale->payments->count() > 1)
                        @foreach ($sale->payments as $sp)
                            <span class="badge bg-secondary-subtle text-dark border me-1">
                                {{ $sp->method_label }}: {{ $currency }}{{ number_format($sp->amount, 2) }}
                            </span>
                        @endforeach
                    @else
                        @php
                            $spMethod = $sale->payment_method ?: 'cash';
                            $spBadge = match($spMethod) {
                                'cash' => 'bg-success',
                                'card' => 'bg-info text-white',
                                'bank_transfer' => 'bg-warning text-dark',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $spBadge }} text-uppercase">{{ str_replace('_', ' ', $spMethod) }}</span>
                        @if ($sale->payment_reference)
                            <small class="text-muted ms-1">({{ $sale->payment_reference }})</small>
                        @endif
                    @endif
                </td>
                <td class="text-end text-success fw-bold">+{{ $currency }}{{ number_format($sale->total_price, 2) }}</td>
                <td>{{ $sale->createdBy?->name ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No sales recorded in this session.') }}</td></tr>
        @endforelse
    </tbody></table></div></div>
</div>
@endsection
