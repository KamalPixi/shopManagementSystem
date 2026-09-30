@extends('layouts.default')

@section('content')
<div class="container-fluid py-4">
    @include('includes.breadcrumb', ['data' => [__('Cash Register / Drawer') => route('cashRegister'), __('Session Details') => '']])
    @php
        $currency = \App\Models\Shop::first()?->currency['symbol'] ?? '€';
        $cashSales = $session->sales->where('payment_method', 'cash')->sum('total_price');
        $cashIn = $session->moneyMovements->where('type', 'in')->sum('amount');
        $cashOut = $session->moneyMovements->where('type', 'out')->sum('amount');
        $expected = (float) $session->opening_balance + $cashSales + $cashIn - $cashOut;
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">{{ __('Cash Register Session Details') }} #{{ $session->id }}</h1>
        <a href="{{ route('cashRegister') }}" class="btn btn-outline-secondary">{{ __('Back to Cash Register') }}</a>
    </div>
    <div class="row g-3 mb-4">
        @foreach ([__('Opening Balance') => $session->opening_balance, __('Cash Sales') => $cashSales, __('Cash In') => $cashIn, __('Cash Out') => $cashOut, __('Expected Closing') => $expected] as $label => $amount)
            <div class="col-md col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted fw-bold">{{ $label }}</small><div class="fs-5 fw-bold">{{ $currency }}{{ number_format($amount, 2) }}</div></div></div></div>
        @endforeach
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
    <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-bold">{{ __('Cash Sales') }}</div><div class="table-responsive"><table class="table table-hover mb-0"><thead class="thead-light"><tr><th>{{ __('Date & Time') }}</th><th>{{ __('Sale') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Created By') }}</th></tr></thead><tbody>
        @forelse ($session->sales->where('payment_method', 'cash')->sortBy('created_at') as $sale)
            <tr><td>{{ $sale->created_at->format('d/m/Y H:i') }}</td><td>#{{ $sale->invoice_no ?? $sale->id }}</td><td class="text-success fw-bold">+{{ $currency }}{{ number_format($sale->total_price, 2) }}</td><td>{{ $sale->createdBy?->name ?? '-' }}</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No cash sales recorded.') }}</td></tr>
        @endforelse
    </tbody></table></div></div>
</div>
@endsection
