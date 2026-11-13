<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Deposit Receipt') }} - {{ $deposit->reference_no }}</title>
    <style>
        html { margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            font-size: 9px;
            padding: 16px 8px;
            line-height: 1.35;
        }
        .company-info {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-info h1 {
            margin: 0;
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .company-info p {
            margin: 1px 0;
            font-size: 8px;
            color: #475569;
        }
        .divider {
            border-top: 1px dashed #94a3b8;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px solid #0f172a;
            margin: 8px 0;
        }
        .receipt-title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 4px 0;
            color: #0f172a;
        }
        .meta-table, .financial-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            font-size: 8px;
            padding: 2px 0;
            vertical-align: top;
        }
        .meta-label {
            color: #64748b;
            width: 40%;
        }
        .meta-value {
            font-weight: bold;
            color: #0f172a;
            width: 60%;
            text-align: right;
        }
        .section-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 6px;
            border-radius: 4px;
            margin: 6px 0;
        }
        .section-title {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 3px;
        }
        .section-text {
            font-size: 8.5px;
            color: #0f172a;
            word-wrap: break-word;
        }
        .financial-table td {
            padding: 3px 0;
            font-size: 9px;
        }
        .financial-label {
            text-align: left;
            color: #475569;
        }
        .financial-value {
            text-align: right;
            font-weight: bold;
            font-family: DejaVu Sans, monospace;
        }
        .balance-row td {
            font-size: 11px;
            font-weight: bold;
            color: #b91c1c;
            padding-top: 5px;
        }
        .badge-method {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            background: #e2e8f0;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 7.5px;
            color: #64748b;
        }
        .footer p { margin: 2px 0; }
    </style>
</head>
<body>
    @php
        $shopTimezone = $shop?->timezone ?? 'Europe/Paris';
        $currencySymbol = $shop?->currency['symbol'] ?? '€';
        $paymentMethodLabel = match (strtolower($deposit->payment_method ?: 'cash')) {
            'cash', 'espece', 'espèces', 'especes' => __('Cash'),
            'card', 'carte', 'cb', 'credit_card', 'carte bancaire' => __('Card'),
            'bank_transfer', 'virement', 'transfer' => __('Bank transfer'),
            'other', 'autre' => __('Other'),
            default => ucfirst(str_replace('_', ' ', $deposit->payment_method)),
        };
    @endphp

    {{-- Company Header --}}
    <div class="company-info">
        <h1>{{ $shop->name ?? 'TECH PARISIEN' }}</h1>
        @if ($shop?->address)
            <p>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }}</p>
        @endif
        <p>{{ __('Tel:') }} {{ $shop->contact_no ?? '-' }} | {{ __('Email') }}: {{ $shop->email ?? '-' }}</p>
    </div>

    <div class="divider"></div>
    <div class="receipt-title">{{ __('Deposit Receipt') }}</div>
    <div class="divider"></div>

    {{-- Meta Details --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">{{ __('Reference') }}:</td>
            <td class="meta-value">{{ $deposit->reference_no }}</td>
        </tr>
        <tr>
            <td class="meta-label">{{ __('Date') }}:</td>
            <td class="meta-value">{{ $deposit->created_at->timezone($shopTimezone)->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="meta-label">{{ __('Staff') }}:</td>
            <td class="meta-value">{{ $deposit->createdBy?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">{{ __('Payment Method') }}:</td>
            <td class="meta-value"><span class="badge-method">{{ $paymentMethodLabel }}</span></td>
        </tr>
        @if ($deposit->payment_reference)
            <tr>
                <td class="meta-label">{{ __('Payment Reference') }}:</td>
                <td class="meta-value">{{ $deposit->payment_reference }}</td>
            </tr>
        @endif
    </table>

    {{-- Customer Details --}}
    <div class="section-box">
        <div class="section-title">{{ __('Customer Details') }}</div>
        <div class="section-text">
            <strong>{{ $deposit->customer_name }}</strong>
            @if ($deposit->customer_phone)
                <br>{{ __('Phone') }}: {{ $deposit->customer_phone }}
            @endif
            @if ($deposit->customer_email)
                <br>{{ __('Email') }}: {{ $deposit->customer_email }}
            @endif
        </div>
    </div>

    {{-- Product / Problem Description --}}
    <div class="section-box">
        <div class="section-title">{{ __('Product / Problem Description') }}</div>
        <div class="section-text" style="white-space: pre-wrap;">{{ $deposit->item_description }}</div>
        @if ($deposit->notes)
            <div class="section-title" style="margin-top: 5px;">{{ __('Notes') }}</div>
            <div class="section-text" style="font-style: italic; color: #475569;">{{ $deposit->notes }}</div>
        @endif
    </div>

    <div class="double-divider"></div>

    {{-- Financial Breakdown --}}
    <table class="financial-table">
        <tr>
            <td class="financial-label">{{ __('Total Amount') }}:</td>
            <td class="financial-value">{{ $currencySymbol }}{{ number_format($deposit->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="financial-label">{{ __('Deposit Amount') }}:</td>
            <td class="financial-value" style="color: #15803d;">- {{ $currencySymbol }}{{ number_format($deposit->deposit_amount, 2) }}</td>
        </tr>
        <tr class="balance-row">
            <td class="financial-label" style="color: #b91c1c;">{{ __('Remaining Balance') }}:</td>
            <td class="financial-value">{{ $currencySymbol }}{{ number_format($deposit->remaining_balance, 2) }}</td>
        </tr>
    </table>

    <div class="double-divider"></div>

    {{-- Footer --}}
    <div class="footer">
        <p style="font-weight: bold; color: #0f172a;">{{ __('Please keep this receipt for collection.') }}</p>
        <p>{{ __('For any inquiry, please call us') }} {{ $shop->contact_no ?? '-' }}</p>
        <p>{{ __('Thank you for your trust!') }}</p>
    </div>
</body>
</html>
