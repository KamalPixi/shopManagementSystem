<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Deposit Receipt') }} {{ $deposit->reference_no }}</title>
    <style>
        @page {
            margin: 28px 32px 32px 32px;
            size: a4 portrait;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f2c59;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .brand-name {
            font-size: 24px;
            font-weight: 800;
            color: #0f2c59;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .contact-info {
            text-align: right;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.4;
        }
        .title-bar {
            background-color: #0f2c59;
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .title-bar table {
            width: 100%;
        }
        .doc-title {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .doc-ref {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            font-family: monospace;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 24px;
            border-collapse: separate;
            border-spacing: 12px 0;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            vertical-align: top;
            width: 50%;
        }
        .box-heading {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f2c59;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .info-row {
            margin-bottom: 4px;
            font-size: 10.5px;
        }
        .info-label {
            color: #64748b;
            display: inline-block;
            width: 110px;
        }
        .info-value {
            font-weight: 600;
            color: #0f172a;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .details-table th {
            background: #0f2c59;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 9px 12px;
        }
        .details-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: top;
        }
        .summary-card {
            width: 320px;
            margin-left: auto;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            padding: 12px 16px;
            margin-bottom: 28px;
        }
        .summary-row {
            display: table;
            width: 100%;
            padding: 4px 0;
            font-size: 11px;
        }
        .summary-label {
            display: table-cell;
            color: #475569;
        }
        .summary-val {
            display: table-cell;
            text-align: right;
            font-weight: 600;
            font-family: monospace;
            font-size: 12px;
        }
        .summary-total {
            border-top: 2px solid #cbd5e1;
            margin-top: 6px;
            padding-top: 8px;
            font-size: 13px;
        }
        .summary-total .summary-label {
            font-weight: bold;
            color: #b91c1c;
        }
        .summary-total .summary-val {
            font-weight: bold;
            color: #b91c1c;
            font-size: 15px;
        }
        .notice-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 14px;
            border-radius: 0 4px 4px 0;
            margin-top: 30px;
            font-size: 9.5px;
            color: #1e3a8a;
        }
        .signatures {
            width: 100%;
            margin-top: 60px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            font-size: 10px;
            color: #475569;
            vertical-align: top;
        }
        .sig-line {
            width: 180px;
            border-top: 1px solid #94a3b8;
            margin: 0 auto 6px auto;
        }
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

    {{-- Top Header --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <div class="brand-name">{{ $shop->name ?? 'TECH PARISIEN' }}</div>
                <div class="brand-subtitle">{{ __('Deposit & Advance Payment Receipt') }}</div>
            </td>
            <td style="width: 45%; vertical-align: middle;" class="contact-info">
                @if ($shop?->address)
                    <div>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }}</div>
                @endif
                <div>{{ __('Tel:') }} {{ $shop->contact_no ?? '-' }} | {{ __('Email') }}: {{ $shop->email ?? '-' }}</div>
            </td>
        </tr>
    </table>

    {{-- Document Title Bar --}}
    <div class="title-bar">
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td class="doc-title">{{ __('Deposit Receipt') }}</td>
                <td class="doc-ref">{{ $deposit->reference_no }}</td>
            </tr>
        </table>
    </div>

    {{-- Information 2-Column Grid --}}
    <table class="info-grid" cellpadding="0" cellspacing="0">
        <tr>
            <td class="info-box">
                <div class="box-heading">{{ __('Customer Information') }}</div>
                <div class="info-row"><span class="info-label">{{ __('Customer') }}:</span> <span class="info-value">{{ $deposit->customer_name }}</span></div>
                <div class="info-row"><span class="info-label">{{ __('Phone') }}:</span> <span class="info-value">{{ $deposit->customer_phone ?: '-' }}</span></div>
                <div class="info-row"><span class="info-label">{{ __('Email') }}:</span> <span class="info-value">{{ $deposit->customer_email ?: '-' }}</span></div>
            </td>
            <td class="info-box">
                <div class="box-heading">{{ __('Deposit & Payment Details') }}</div>
                <div class="info-row"><span class="info-label">{{ __('Date & Time') }}:</span> <span class="info-value">{{ $deposit->created_at->timezone($shopTimezone)->format('d/m/Y H:i') }}</span></div>
                <div class="info-row"><span class="info-label">{{ __('Staff / Cashier') }}:</span> <span class="info-value">{{ $deposit->createdBy?->name ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">{{ __('Payment Method') }}:</span> <span class="info-value">{{ $paymentMethodLabel }}</span></div>
                @if ($deposit->payment_reference)
                    <div class="info-row"><span class="info-label">{{ __('Reference') }}:</span> <span class="info-value">{{ $deposit->payment_reference }}</span></div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Item / Problem Description Table --}}
    <table class="details-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 65%;">{{ __('Product / Problem Description') }}</th>
                <th style="width: 30%; text-align: right;">{{ __('Agreed Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td>
                    <div style="font-weight: 600; font-size: 11.5px; margin-bottom: 4px;">
                        {{ $deposit->item_description }}
                    </div>
                    @if ($deposit->notes)
                        <div style="font-size: 9.5px; color: #64748b; font-style: italic; margin-top: 4px;">
                            {{ __('Notes') }}: {{ $deposit->notes }}
                        </div>
                    @endif
                </td>
                <td style="text-align: right; font-family: monospace; font-size: 12px; font-weight: bold;">
                    {{ $currencySymbol }}{{ number_format($deposit->total_amount, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Financial Summary Box --}}
    <div class="summary-card">
        <div class="summary-row">
            <span class="summary-label">{{ __('Total Estimated / Agreed Amount') }}:</span>
            <span class="summary-val">{{ $currencySymbol }}{{ number_format($deposit->total_amount, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label" style="color: #15803d;">{{ __('Deposit / Advance Paid') }}:</span>
            <span class="summary-val" style="color: #15803d;">- {{ $currencySymbol }}{{ number_format($deposit->deposit_amount, 2) }}</span>
        </div>
        <div class="summary-row summary-total">
            <span class="summary-label">{{ __('Remaining Balance Due') }}:</span>
            <span class="summary-val">{{ $currencySymbol }}{{ number_format($deposit->remaining_balance, 2) }}</span>
        </div>
    </div>

    {{-- Terms Notice --}}
    <div class="notice-box">
        <strong>{{ __('Important Notice') }}:</strong>
        {{ __('Please retain this deposit receipt. It must be presented upon collecting your device or completed order. The remaining balance must be paid in full prior to release.') }}
    </div>

    {{-- Signatures --}}
    <table class="signatures" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="sig-line"></div>
                <div>{{ __('Customer Signature') }}</div>
            </td>
            <td>
                <div class="sig-line"></div>
                <div>{{ __('Authorized Shop Signature') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
