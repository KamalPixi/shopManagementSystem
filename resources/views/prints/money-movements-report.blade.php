<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Movement History Log Report') }}</title>

    <style>
        @page {
            margin: 12px 15px 25px 15px;
        }
        html {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.25;
            padding: 10px 14px;
        }
        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }
        .text-start { text-align: left !important; }
        .text-muted { color: #64748b; }
        .fw-bold { font-weight: bold; }
        .text-success { color: #15803d !important; }
        .text-danger { color: #b91c1c !important; }
        .text-primary { color: #0f2c59 !important; }

        /* Company Header */
        .company-header {
            text-align: center;
            border-bottom: 1.5px solid #0f2c59;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .company-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f2c59;
            margin: 0 0 2px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .company-meta {
            font-size: 8px;
            color: #475569;
            line-height: 1.2;
            margin: 1px 0;
        }

        /* Report Title & Metadata Box */
        .report-title-bar {
            text-align: center;
            margin-bottom: 8px;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f2c59;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 0 0 4px 0;
        }

        .filter-badges-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .filter-badges-table td {
            padding: 4px 8px;
            font-size: 8px;
            color: #334155;
            vertical-align: middle;
        }
        .filter-label {
            font-weight: bold;
            color: #0f2c59;
        }

        /* Category Breakdown Mini Table */
        .category-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 9px;
            font-size: 7.5px;
        }
        .category-summary-table th {
            background-color: #e2e8f0;
            color: #0f2c59;
            font-weight: bold;
            padding: 3px 6px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
        }
        .category-summary-table td {
            padding: 2.5px 6px;
            border: 1px solid #e2e8f0;
        }

        /* Movement History Log Table */
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .movements-table thead {
            display: table-header-group;
        }
        .movements-table tr {
            page-break-inside: avoid;
        }
        .movements-table th {
            background-color: #0f2c59;
            color: #ffffff;
            font-weight: bold;
            padding: 4px 5px;
            border: 1px solid #0f2c59;
            text-transform: uppercase;
            font-size: 7.5px;
            letter-spacing: 0.2px;
        }
        .movements-table td {
            padding: 3px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            line-height: 1.2;
        }
        .movements-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .movements-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        /* Badges */
        .badge-type-in {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            text-transform: uppercase;
            border: 0.5px solid #86efac;
        }
        .badge-type-out {
            display: inline-block;
            background-color: #fee2e2;
            color: #b91c1c;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            text-transform: uppercase;
            border: 0.5px solid #fca5a5;
        }

        /* Table Footer */
        .movements-table tfoot td {
            background-color: #e2e8f0;
            font-weight: bold;
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
            font-size: 8px;
        }

        /* Page Footer */
        .page-footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #cbd5e1;
            font-size: 7px;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    @php
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
    @endphp

    {{-- Company Header --}}
    <div class="company-header">
        <div class="company-title">{{ $shop?->name ?? 'TECH PARISIEN' }}</div>
        @if ($shop?->address || $shop?->city)
            <div class="company-meta">{{ $shop?->address }}{{ $shop?->city ? ', ' . $shop->city : '' }}{{ $shop?->postal_code ? ' ' . $shop->postal_code : '' }}{{ $shop?->country ? ' - ' . $shop->country : '' }}</div>
        @endif
        <div class="company-meta">
            @if ($shop?->contact_no) <strong>{{ __('Tel:') }}</strong> {{ $shop->contact_no }} @endif
            @if ($shop?->email) | <strong>{{ __('Email:') }}</strong> {{ $shop->email }} @endif
            @if ($shop?->registration_no) | <strong>{{ __('Siret:') }}</strong> {{ $shop->registration_no }} @endif
        </div>
    </div>

    {{-- Report Title --}}
    <div class="report-title-bar">
        <div class="report-title">{{ __('Movement History Log Report') }}</div>
    </div>

    {{-- Filter Metadata Bar --}}
    <table class="filter-badges-table">
        <tr>
            <td style="width: 32%;">
                <span class="filter-label">{{ __('Period') }}:</span>
                @if (!empty($filter_start_date) || !empty($filter_end_date))
                    {{ $filter_start_date ?: '-' }} {{ __('to') }} {{ $filter_end_date ?: '-' }}
                @else
                    {{ __('All Dates') }}
                @endif
            </td>
            <td style="width: 22%;">
                <span class="filter-label">{{ __('Type') }}:</span>
                {{ $filter_type_label ?? __('All Types') }}
            </td>
            <td style="width: 26%;">
                <span class="filter-label">{{ __('Category') }}:</span>
                {{ $filter_category_name ?? __('All Categories') }}
            </td>
            <td style="width: 20%;" class="text-end">
                <span class="filter-label">{{ __('Print Date') }}:</span>
                {{ $print_date ?? date('d/m/Y H:i') }}
            </td>
        </tr>
        @if (!empty($filter_user_name))
        <tr>
            <td colspan="4" style="border-top: 1px dashed #cbd5e1; padding-top: 2px;">
                <span class="filter-label">{{ __('Staff / Done By') }}:</span>
                {{ $filter_user_name }}
            </td>
        </tr>
        @endif
    </table>

    {{-- Optional Compact Category Breakdown --}}
    @if (!empty($categoryBreakdown) && (count($categoryBreakdown['in'] ?? []) > 0 || count($categoryBreakdown['out'] ?? []) > 0))
        <table class="category-summary-table">
            <thead>
                <tr>
                    <th style="width: 50%;" class="text-start">{{ __('Cash In by Category') }}</th>
                    <th style="width: 50%;" class="text-start">{{ __('Cash Out by Category') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="vertical-align: top;">
                        @if (!empty($categoryBreakdown['in']))
                            @foreach ($categoryBreakdown['in'] as $cName => $cAmt)
                                <div style="display: flex; justify-content: space-between; padding: 1px 0;">
                                    <span>&bull; {{ __($cName) }}:</span>
                                    <strong class="text-success">+{{ $currencySymbol }} {{ number_format($cAmt, 2) }}</strong>
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted">{{ __('No cash in recorded') }}</span>
                        @endif
                    </td>
                    <td style="vertical-align: top;">
                        @if (!empty($categoryBreakdown['out']))
                            @foreach ($categoryBreakdown['out'] as $cName => $cAmt)
                                <div style="display: flex; justify-content: space-between; padding: 1px 0;">
                                    <span>&bull; {{ __($cName) }}:</span>
                                    <strong class="text-danger">-{{ $currencySymbol }} {{ number_format($cAmt, 2) }}</strong>
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted">{{ __('No cash out recorded') }}</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- Movement History Table --}}
    <table class="movements-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">#</th>
                <th style="width: 12%;" class="text-start">{{ __('Date & Time') }}</th>
                <th style="width: 9%;" class="text-center">{{ __('Type') }}</th>
                <th style="width: 17%;" class="text-start">{{ __('Category') }}</th>
                <th style="width: 12%;" class="text-end">{{ __('Amount') }}</th>
                <th style="width: 25%;" class="text-start">{{ __('Reason / Remark') }}</th>
                <th style="width: 11%;" class="text-start">{{ __('Done By') }}</th>
                <th style="width: 11%;" class="text-center">{{ __('Register') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movements as $index => $movement)
                @php
                    $isIn = $movement->type === 'in';
                    $catName = $movement->category?->name ?? __('Uncategorized');
                    $registerSession = $movement->cash_register_session_id ? ('Session #' . $movement->cash_register_session_id) : __('Outside');
                @endphp
                <tr>
                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                    <td class="text-start">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        @if ($isIn)
                            <span class="badge-type-in">+ {{ __('In') }}</span>
                        @else
                            <span class="badge-type-out">- {{ __('Out') }}</span>
                        @endif
                    </td>
                    <td class="text-start fw-bold">
                        {{ __($catName) }}
                    </td>
                    <td class="text-end fw-bold {{ $isIn ? 'text-success' : 'text-danger' }}">
                        {{ $isIn ? '+' : '-' }}{{ $currencySymbol }} {{ number_format($movement->amount, 2) }}
                    </td>
                    <td class="text-start" style="color: #334155;">
                        {{ $movement->reason ?: '-' }}
                    </td>
                    <td class="text-start text-muted">
                        {{ $movement->user?->name ?? ($movement->user?->username ?? '-') }}
                    </td>
                    <td class="text-center text-muted" style="font-size: 7.5px;">
                        {{ $registerSession }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted" style="padding: 15px;">
                        {{ __('No money movements found for the selected filter criteria.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-end fw-bold">{{ __('Totals for filtered period') }}:</td>
                <td class="text-end fw-bold">
                    <span class="text-success">+{{ $currencySymbol }} {{ number_format($totalIn, 2) }}</span><br>
                    <span class="text-danger">-{{ $currencySymbol }} {{ number_format($totalOut, 2) }}</span>
                </td>
                <td colspan="3" class="text-start fw-bold">
                    {{ __('Net Flow') }}:
                    <span class="{{ $netCash >= 0 ? 'text-primary' : 'text-danger' }}">
                        {{ $netCash >= 0 ? '+' : '' }}{{ $currencySymbol }} {{ number_format($netCash, 2) }}
                    </span>
                    <span class="text-muted fw-normal" style="margin-left: 6px;">({{ $movements->count() }} {{ __('entries') }})</span>
                </td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
