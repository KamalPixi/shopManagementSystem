@extends('layouts.default')

@section('content')
  @php
      $currencySymbol = $shop?->currency['symbol'] ?? '€';
  @endphp

  @include('includes.breadcrumb', ['data' => ['Dashboard' => '']])

  {{-- Dashboard Header --}}
  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
      <div>
          <h1 class="h4 fw-bold text-dark mb-0">{{ __('Dashboard Overview') }}</h1>
          <p class="text-muted small mb-0">{{ now()->format('l, d F Y') }}</p>
      </div>
      <div class="btn-toolbar mb-2 mb-md-0 d-flex align-items-center gap-2">
          <a href="{{ route('cashRegister') }}" class="btn btn-sm btn-outline-gray-600 d-inline-flex align-items-center">
              <i class="fa-solid fa-cash-register me-2 text-muted"></i>{{ __('Cash Register') }}
          </a>
          <a href="{{ route('pos') }}" class="btn btn-sm btn-primary d-inline-flex align-items-center">
              <i class="fa-solid fa-cart-shopping me-2"></i>{{ __('Open POS') }}
          </a>
      </div>
  </div>

  {{-- Overdue Register Alert if past day wasn't closed --}}
  @php
      $overdueSession = \App\Models\CashRegisterSession::previousDayUnclosedSession();
  @endphp
  @if ($overdueSession)
      <div class="alert alert-warning border-start border-4 border-warning d-flex align-items-center justify-content-between p-3 mb-4 rounded shadow-sm" role="alert">
          <div class="d-flex align-items-center">
              <i class="fa-solid fa-triangle-exclamation text-warning fs-3 me-3"></i>
              <div>
                  <strong class="d-block text-dark">{{ __('Unclosed Register From Previous Day!') }}</strong>
                  <span class="small text-muted">{{ __('The cash register opened on :date was not closed. You must close yesterday\'s drawer before opening today.', ['date' => $overdueSession->opened_at->format('d M Y, h:i A')]) }}</span>
              </div>
          </div>
          <a href="{{ route('cashRegister') }}" class="btn btn-sm btn-warning text-dark fw-bold px-3">
              <i class="fa-solid fa-lock me-1"></i> {{ __('Close Drawer') }}
          </a>
      </div>
  @endif

  {{-- At-a-Glance Clean KPI Statistics --}}
  <x-statistics />

  {{-- Two Column Layout: Recent Sales & Low Stock Products --}}
  <div class="row g-4">
      {{-- Column 1: Recent Sales --}}
      <div class="col-12 col-xl-7">
          <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                  <h6 class="mb-0 fw-bold d-flex align-items-center text-dark">
                      <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> {{ __('Recent Sales') }}
                  </h6>
                  <a href="{{ route('sales.index') }}" class="btn btn-xs btn-outline-primary px-3">
                      {{ __('View All') }} <i class="fa-solid fa-arrow-right ms-1"></i>
                  </a>
              </div>
              <div class="card-body p-0">
                  <div class="table-responsive">
                      <table class="table table-centered table-nowrap table-hover mb-0">
                          <thead class="thead-light">
                              <tr>
                                  <th class="border-0 px-3 py-2">{{ __('Receipt #') }}</th>
                                  <th class="border-0 py-2">{{ __('Payment') }}</th>
                                  <th class="border-0 py-2 text-center">{{ __('Items') }}</th>
                                  <th class="border-0 py-2 text-end">{{ __('Total') }}</th>
                                  <th class="border-0 px-3 py-2 text-end">{{ __('Time') }}</th>
                              </tr>
                          </thead>
                          <tbody>
                              @forelse ($recentSales as $sale)
                                  <tr>
                                      <td class="px-3 py-2 align-middle">
                                          <span class="badge bg-dark font-monospace px-2 py-1">{{ $sale->receipt_no }}</span>
                                      </td>
                                      <td class="py-2 align-middle">
                                          @if ($sale->payment_method === 'cash')
                                              <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                  <i class="fa-solid fa-money-bill-wave me-1"></i>{{ __('Cash') }}
                                              </span>
                                          @elseif ($sale->payment_method === 'card')
                                              <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                                  <i class="fa-solid fa-credit-card me-1"></i>{{ __('Card') }}
                                              </span>
                                          @else
                                              <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                                  <i class="fa-solid fa-building-columns me-1"></i>{{ __('Bank') }}
                                              </span>
                                          @endif
                                      </td>
                                      <td class="py-2 align-middle text-center">
                                          <span class="badge bg-gray-200 text-gray-800 border px-2 py-1 fw-bold">{{ $sale->saleItems->count() }}</span>
                                      </td>
                                      <td class="py-2 align-middle text-end fw-bold text-dark">
                                          {{ $currencySymbol }}{{ number_format($sale->total_price, 2) }}
                                      </td>
                                      <td class="px-3 py-2 align-middle text-end text-muted small">
                                          {{ $sale->created_at->diffForHumans() }}
                                      </td>
                                  </tr>
                              @empty
                                  <tr>
                                      <td colspan="5" class="text-center py-4 text-muted">
                                          {{ __('No sales recorded yet.') }}
                                      </td>
                                  </tr>
                              @endforelse
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>

      {{-- Column 2: Low Stock Alerts --}}
      <div class="col-12 col-xl-5">
          <div class="card border-0 shadow-sm h-100">
              <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                  <h6 class="mb-0 fw-bold d-flex align-items-center text-dark">
                      <i class="fa-solid fa-triangle-exclamation me-2 text-warning"></i> {{ __('Low Stock Alerts') }}
                      @if ($lowStockCount > 0)
                          <span class="badge bg-danger ms-2">{{ $lowStockCount }}</span>
                      @endif
                  </h6>
                  <a href="{{ route('stocks.index') }}" class="btn btn-xs btn-outline-secondary px-3">
                      {{ __('Manage Stock') }} <i class="fa-solid fa-arrow-right ms-1"></i>
                  </a>
              </div>
              <div class="card-body p-0">
                  <div class="table-responsive">
                      <table class="table table-centered table-nowrap table-hover mb-0">
                          <thead class="thead-light">
                              <tr>
                                  <th class="border-0 px-3 py-2">{{ __('Product') }}</th>
                                  <th class="border-0 py-2">{{ __('Category') }}</th>
                                  <th class="border-0 px-3 py-2 text-end">{{ __('Remaining') }}</th>
                              </tr>
                          </thead>
                          <tbody>
                              @forelse ($products as $product)
                                  <tr>
                                      <td class="px-3 py-2 align-middle">
                                          <div class="fw-bold text-dark text-truncate" style="max-width: 170px;" title="{{ $product->name }}">
                                              {{ $product->name }}
                                          </div>
                                          @if ($product->sku)
                                              <small class="text-muted font-monospace d-block" style="font-size: 0.72rem;">{{ $product->sku }}</small>
                                          @endif
                                      </td>
                                      <td class="py-2 align-middle text-muted small">
                                          {{ $product->category?->name ?? '-' }}
                                      </td>
                                      <td class="px-3 py-2 align-middle text-end">
                                          @if ($product->quantity <= 0)
                                              <span class="badge bg-danger px-2 py-1">{{ __('Out of Stock') }}</span>
                                          @elseif ($product->quantity < 5)
                                              <span class="badge bg-danger px-2 py-1">{{ $product->quantity }} {{ __('left') }}</span>
                                          @else
                                              <span class="badge bg-warning text-dark px-2 py-1">{{ $product->quantity }} {{ __('left') }}</span>
                                          @endif
                                      </td>
                                  </tr>
                              @empty
                                  <tr>
                                      <td colspan="3" class="text-center py-4 text-muted">
                                          <i class="fa-solid fa-circle-check text-success me-1"></i> {{ __('All products have sufficient stock.') }}
                                      </td>
                                  </tr>
                              @endforelse
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>
  </div>
@endsection
