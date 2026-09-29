<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Invoice') }} {{ $sale->receipt_no }}</title>
    <style>
        @page {
            margin: 24px 28px 28px 28px;
            size: a4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
            padding: 0;
            min-height: calc(297mm - 52px);
            position: relative;
            padding-bottom: 36px;
        }
        .w-100 { width: 100%; }
        .text-start { text-align: left; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Top Header */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f2c59;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .brand-name {
            font-size: 26px;
            font-weight: 800;
            color: #0f2c59;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .brand-slogan {
            font-size: 8px;
            font-weight: bold;
            color: #e11d48;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .shop-logo {
            max-height: 72px;
            max-width: 360px;
            object-fit: contain;
            margin-bottom: 4px;
        }
        .top-contact-info {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
            text-align: left !important;
            padding-left: 18px;
        }
        .contact-panel {
            border-left: 1px solid #0f2c59;
            padding-left: 18px;
            padding-bottom: 6px;
            text-align: left !important;
        }
        .contact-row {
            display: block;
            position: relative;
            width: 100%;
            min-height: 22px;
            margin: 4px 0;
            padding-left: 32px;
            text-align: left !important;
        }
        .contact-icon {
            display: block;
            position: absolute;
            left: 0;
            top: 0;
            width: 22px;
            height: 22px;
            max-width: 22px;
            max-height: 22px;
            object-fit: contain;
        }
        .contact-value {
            display: block;
            line-height: 22px;
            padding-left: 0;
            text-align: left !important;
            color: #1e293b;
        }

        /* Legal & Meta Section */
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .company-legal-box {
            font-size: 9.5px;
            color: #334155;
            line-height: 1.4;
            font-weight: 400;
        }
        .company-legal-title {
            font-weight: bold;
            font-size: 20px;
            color: #0f2c59;
            margin-bottom: 5px;
            line-height: 1.1;
        }
        .company-legal-label {
            font-weight: 700;
            color: #0f172a;
        }
        .company-legal-label-normal {
            font-weight: 400;
            color: #334155;
        }
        .company-legal-value {
            font-weight: 400;
            color: #334155;
        }
        .facture-box {
            background-color: #eef2f7;
            border-radius: 5px;
            padding: 10px 14px;
            margin-top: -10px;
            width: 100%;
        }
        .facture-title {
            font-size: 27px;
            font-weight: 900;
            color: #0f2c59;
            letter-spacing: 1px;
            margin: 0 0 6px 0;
            text-align: right;
            text-transform: uppercase;
            position: relative;
            top: -8px;
        }
        .facture-meta-row {
            font-size: 9.5px;
            color: #334155;
            display: grid;
            grid-template-columns: 122px 12px 1fr;
            align-items: baseline;
            text-align: left;
            padding: 1px 0;
        }
        .facture-meta-label {
            font-weight: 700;
        }
        .facture-meta-colon {
            text-align: center;
        }

        /* Client Section */
        .client-card {
            background-color: #eef2f7;
            border-left: 0;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 0 4px 4px 0;
        }
        .client-card-title {
            font-size: 10px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 3px;
        }
        .client-name {
            font-size: 12px;
            font-weight: bold;
            color: #0f2c59;
        }
        .client-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin: 2px 0;
            font-size: 10px;
        }
        .client-label {
            display: table-cell;
            width: 16%;
            font-weight: bold;
            color: #0f2c59;
        }
        .client-colon {
            display: table-cell;
            width: 3%;
            font-weight: bold;
            color: #1e293b;
        }
        .client-value {
            display: table-cell;
            width: 81%;
            color: #1e293b;
        }
        .client-details {
            font-size: 9.5px;
            color: #475569;
        }

        /* Products Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table thead th {
            background-color: #0f2c59;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            padding: 9px 6px;
            border: 1px solid #0f2c59;
        }
        .items-table tbody td {
            padding: 6px;
            font-size: 9.5px;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .product-title {
            font-weight: bold;
            color: #0f2c59;
            font-size: 10px;
        }
        .product-meta {
            font-size: 8.5px;
            color: #64748b;
            line-height: 1.3;
            margin-top: 2px;
        }
        .product-meta b {
            color: #334155;
        }
        .warranty-meta {
            display: block;
            margin-top: 8px;
            color: #0f2c59;
            line-height: 1.35;
        }
        .badge-vat {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            font-size: 7.5px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 3px;
            margin-left: 4px;
        }

        /* Bottom Section */
        .bottom-table {
            width: 100%;
            margin-top: 5px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .payment-mode-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            height: auto;
            page-break-inside: avoid;
        }
        .payment-mode-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f2c59;
            margin-bottom: 8px;
        }
        .payment-option {
            font-size: 10.5px;
            color: #334155;
            margin-bottom: 7px;
            line-height: 16px;
        }
        .checkbox-box {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 1px solid #475569;
            text-align: center;
            line-height: 14px;
            font-size: 9px;
            font-weight: bold;
            margin-right: 7px;
            background: #fff;
            vertical-align: middle;
            position: relative;
            top: -1px;
        }
        .checkbox-box.checked {
            background-color: #0f2c59;
            color: #ffffff;
            border-color: #0f2c59;
        }
        .checkbox-box.checked {
            padding: 0;
            border: 0;
            background: transparent;
        }
        .checkbox-box .payment-tick-icon {
            display: block;
            width: 22.5px;
            height: 22.5px;
            object-fit: contain;
        }
        .payment-other-line {
            display: inline-block;
            width: 125px;
            margin-left: 5px;
            border-bottom: 1px solid #0f2c59;
            height: 10px;
            vertical-align: middle;
        }

        /* Totals Card */
        .totals-card {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-card td {
            padding: 6px 8px;
            font-size: 10.5px;
        }
        .totals-label {
            color: #475569;
            text-align: left;
            font-weight: bold;
        }
        .totals-value {
            font-weight: normal;
            color: #1e293b;
            text-align: right;
        }
        .grand-total-row td {
            background-color: #0f2c59;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 14px;
            padding: 9px 10px;
        }
        .grand-total-row td:first-child {
            border-radius: 0 0 0 4px;
        }
        .grand-total-row td:last-child {
            border-radius: 0 0 4px 0;
        }
        .grand-total-row .totals-label {
            color: #ffffff;
        }
        .grand-total-row .totals-value {
            color: #ffffff;
        }

        /* Footer */
        .footer-section {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            text-align: center;
            border-top: 1px solid #0f2c59;
            padding-top: 12px;
            page-break-inside: avoid;
        }
        .signature-msg {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-style: italic;
            font-size: 22px;
            color: #0f2c59;
            margin-bottom: 2px;
        }
        .signature-team {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 12px;
        }
        .services-bar {
            font-size: 11px;
            font-weight: 400;
            color: #0f2c59;
            padding: 0;
            letter-spacing: 0.2px;
        }
        .services-bar span {
            color: #c81e32;
            margin: 0 12px;
        }
        .system-note {
            font-size: 7.5px;
            color: #94a3b8;
            margin-top: 6px;
        }
    </style>
</head>
<body>

    @php
        $gstPercent = (float) ($shop?->gst_percent ?? 0);

        // POS prices are tax exclusive. Sale item totals include item discounts.
        $totalTtc = (float) ($sale->total_price ?? 0);
        $totalGst = (float) ($sale->gst ?? 0);
        $discount = (float) ($sale->discount ?? 0);
        $subtotalHt = $sale->saleItems->sum(fn ($item) => (float) $item->price * (int) $item->quantity);
        $totalHt = $sale->saleItems->sum(fn ($item) => (float) $item->total);
        $paymentMethod = strtolower($sale->payment_method ?? 'cash');
        $otherPaymentDetails = $paymentMethod === 'other' ? trim((string) ($sale->payment_reference ?? '')) : '';
        $staffName = $sale->createdBy?->name ?? __('Unknown');
        $taxLabel = $shop?->tax_label ?? 'VAT';
        $currencyCode = $shop?->currency['code'] ?? 'EUR';
        $currencySymbol = $shop?->currency['symbol'] ?? $currencyCode;
        $tickIcon = file_exists(public_path('assets/icons/tick.png')) ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/icons/tick.png'))) : null;
        $legal = $shop?->settings['legal'] ?? [];
        $legalCompanyType = $legal['company_type'] ?? 'SAS au capital de 2 010 €';
        $legalAddress = $shop?->address ?: '33 rue Brézin, 75014 Paris, France';
        $legalRegistration = $legal['registration_number'] ?? '109 915 405';
        $legalTaxNumber = $legal['tax_number'] ?? 'FR75 109915405';
        $paymentLabels = [
            'cash' => __('Cash'), 'card' => __('Card'),
            'bank_transfer' => __('Bank transfer'),
        ];
        $logoPath = public_path('assets/img/tp-horizontal.png');
        $logoData = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $locationIcon = file_exists(public_path('assets/icons/location.png')) ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/icons/location.png'))) : null;
        $contactIcon = file_exists(public_path('assets/icons/contact.png')) ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/icons/contact.png'))) : null;
        $emailIcon = file_exists(public_path('assets/icons/email.png')) ? 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('assets/icons/email.png'))) : null;
    @endphp

    <!-- Header Table -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <div style="padding-left: 18px;">
                @if ($logoData)
                    <img src="{{ $logoData }}" alt="{{ $shop->name ?? 'Tech Parisien' }}" class="shop-logo">
                @else
                    <div class="brand-name">{{ $shop->name ?? 'TECH PARISIEN' }}</div>
                    <div class="brand-slogan">{{ __('Buy · Sell · Exchange') }}</div>
                @endif
                </div>
            </td>
            <td style="width: 45%; vertical-align: middle;" class="top-contact-info">
                <div class="contact-panel">
                @php
                    $contactAddress = $shop?->address ?: '33 rue Brézin, 75014 Paris, France';
                    $contactPhone = $shop?->contact_no ?: '07 53 74 59 97';
                    $contactEmail = $shop?->email ?: 'techparisien@gmail.com';
                @endphp
                <div class="contact-row"><img src="{{ $locationIcon }}" class="contact-icon"><span class="contact-value">{{ $contactAddress }}@if(!$shop?->address && !$shop?->postal_code && !$shop?->city)@else{{ $shop?->postal_code || $shop?->city ? ', ' . trim(($shop?->postal_code ?? '') . ' ' . ($shop?->city ?? '')) : '' }}{{ $shop?->country ? ', ' . $shop->country : '' }}@endif</span></div>
                <div class="contact-row"><img src="{{ $contactIcon }}" class="contact-icon"><span class="contact-value">{{ $contactPhone }}</span></div>
                <div class="contact-row"><img src="{{ $emailIcon }}" class="contact-icon"><span class="contact-value">{{ $contactEmail }}</span></div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Legal & Invoice Meta Section -->
    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 52%; vertical-align: top; padding-right: 15px;" class="company-legal-box">
                <div class="company-legal-title">{{ $shop->name ?? 'TECH PARISIEN' }}</div>
                <div class="company-legal-value" style="font-weight: 700; color: #0f172a;">{{ $legalCompanyType }}</div>
                <div class="company-legal-value">{{ $legalAddress }}</div>
                <div><span class="company-legal-label">{{ __('SIREN') }} :</span> <span class="company-legal-value">{{ $legalRegistration }}</span></div>
                <div><span class="company-legal-label-normal">TVA intracommunautaire:</span> <span class="company-legal-value">{{ $legalTaxNumber }}</span></div>
            </td>
            <td style="width: 48%; vertical-align: top;">
                <div class="facture-title">{{ __('Invoice') }}</div>
                <div class="facture-box">
                    <div class="facture-meta-row"><span class="facture-meta-label">{{ __('Invoice no.') }}</span><span class="facture-meta-colon">:</span><span>{{ $sale->receipt_no }}</span></div>
                    <div class="facture-meta-row"><span class="facture-meta-label">{{ __('Date') }}</span><span class="facture-meta-colon">:</span><span>{{ $sale->created_at->format('d/m/Y') }}</span></div>
                    <div class="facture-meta-row"><span class="facture-meta-label">{{ __('Staff') }}</span><span class="facture-meta-colon">:</span><span>{{ $staffName }}</span></div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Client Info -->
    <div class="client-card">
        <div class="client-row"><span class="client-label">{{ __('Customer') }}</span><span class="client-colon">:</span><span class="client-value">{{ $sale->customer?->name ?: __('Walk-in customer') }}</span></div>
        <div class="client-row"><span class="client-label">{{ __('Address') }}</span><span class="client-colon">:</span><span class="client-value">{{ collect([$sale->customer?->address, trim(($sale->customer?->postal_code ?? '') . ' ' . ($sale->customer?->city ?? '')), $sale->customer?->country])->filter()->implode(', ') ?: '-' }}</span></div>
        <div class="client-row"><span class="client-label">{{ __('Email') }}</span><span class="client-colon">:</span><span class="client-value">{{ $sale->customer?->email ?: '-' }}</span></div>
    </div>

    <!-- Products Table -->
    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">#</th>
                <th style="width: 44%; text-align: left;">{{ __('Product description') }}</th>
                <th style="width: 14%; text-align: right;">{{ __('Unit price excl. tax') }} ({{ $currencySymbol }})</th>
                <th style="width: 8%; text-align: center;">{{ $taxLabel }} (%)</th>
                <th style="width: 14%; text-align: right;">{{ __('Unit price incl. tax') }} ({{ $currencySymbol }})</th>
                <th style="width: 4%; text-align: center;">{{ __('Qty') }}</th>
                <th style="width: 12%; text-align: right;">{{ __('Total incl. tax') }} ({{ $currencySymbol }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $index => $item)
                @php
                    $isVat = $item->is_vatable;
                    $itemVatRate = $isVat ? $gstPercent : 0;
                    $unitHt = (float) $item->price;
                    $unitTtc = $unitHt * (1 + $itemVatRate / 100);
                    $totalLineTtc = (float) $item->total * (1 + $itemVatRate / 100);
                    $warrantyText = $item->warranty_description ?: ($item->warranty?->description ?: $item->warranty?->name);
                    $sn = $item->productVariation?->serial_no ?: $item->product?->serial_no;
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-title">
                            {{ $item->name }}
                            @if($isVat && $itemVatRate > 0)
                                <span class="badge-vat">{{ $taxLabel }} {{ $itemVatRate }}%</span>
                            @endif
                        </div>
                        <div class="product-meta">
                            @if ($item->product?->model_no)
                                <br><span><b>{{ __('Model') }}:</b> {{ $item->product?->model_no }}</span>
                            @endif
                            @if ($sn && $item->show_serial_on_receipt)
                                <span><b>SN / IMEI :</b> {{ $sn }}</span>
                            @endif
                            @foreach ($item->attributes_and_values ?? [] as $att => $value)
                                @php
                                    $attributeName = is_array($value) ? ($value['name'] ?? '') : $att;
                                    $attributeValue = is_array($value) ? ($value['value'] ?? '') : $value;
                                @endphp
                                @if($attributeName && $attributeValue)
                                    <br><span><b>{{ ucfirst($attributeName) }} :</b> {{ ucfirst($attributeValue) }}</span>
                                @endif
                            @endforeach
                            @if ($warrantyText && $item->show_warranty_on_receipt)
                                <span class="warranty-meta"><b>{{ __('Warranty') }}:</b> {{ $warrantyText }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">{{ number_format($unitHt, 2) }}</td>
                    <td class="text-center">{{ $itemVatRate > 0 ? $itemVatRate.'%' : '0%' }}</td>
                    <td class="text-end">{{ number_format($unitTtc, 2) }}</td>
                    <td class="text-center font-bold">{{ $item->quantity }}</td>
                    <td class="text-end font-bold">{{ number_format($totalLineTtc, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Bottom Section: Payment Methods & Totals -->
    <table class="bottom-table" cellpadding="0" cellspacing="0">
        <tr>
            <!-- Payment Mode Checklist -->
            <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                <div class="payment-mode-box">
                    <div class="payment-mode-title">{{ __('Payment method') }}</div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['cash', 'especes', 'espèce', 'espece']) ? 'checked' : '' }}">@if(in_array($paymentMethod, ['cash', 'especes', 'espèce', 'espece']) && $tickIcon)<img src="{{ $tickIcon }}" class="payment-tick-icon">@else&nbsp;@endif
                        </span>
                        {{ __('Cash') }}
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['card', 'carte', 'cb', 'credit_card', 'carte bancaire']) ? 'checked' : '' }}">@if(in_array($paymentMethod, ['card', 'carte', 'cb', 'credit_card', 'carte bancaire']) && $tickIcon)<img src="{{ $tickIcon }}" class="payment-tick-icon">@else&nbsp;@endif
                        </span>
                        {{ __('Card') }}
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['bank_transfer', 'virement', 'transfer']) ? 'checked' : '' }}">@if(in_array($paymentMethod, ['bank_transfer', 'virement', 'transfer']) && $tickIcon)<img src="{{ $tickIcon }}" class="payment-tick-icon">@else&nbsp;@endif
                        </span>
                        {{ __('Bank transfer') }}
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['cheque', 'check', 'other', 'autre']) ? 'checked' : '' }}">@if(in_array($paymentMethod, ['cheque', 'check', 'other', 'autre']) && $tickIcon)<img src="{{ $tickIcon }}" class="payment-tick-icon">@else&nbsp;@endif
                        </span>
                            {{ __('Other') }} : @if($otherPaymentDetails)<span>{{ $otherPaymentDetails }}</span>@else<span class="payment-other-line"></span>@endif
                    </div>
                    @if($sale->payment_reference)
                        <div style="font-size: 8.5px; color: #64748b; margin-top: 6px;">
                            <strong>{{ __('Reference') }}:</strong> {{ $sale->payment_reference }}
                        </div>
                    @endif
                </div>
            </td>

            <!-- Totals Breakdown -->
            <td style="width: 50%; vertical-align: top;">
                <table class="totals-card">
                    <tr>
                        <td class="totals-label">{{ __('Subtotal excl. tax') }}:</td>
                        <td class="totals-value">{{ number_format($subtotalHt, 2) }}</td>
                    </tr>
                    @if($discount > 0)
                    <tr>
                        <td class="totals-label" style="color: #e11d48;">{{ __('Discount') }}:</td>
                        <td class="totals-value" style="color: #e11d48;">- {{ number_format($discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="totals-label">{{ __('Total excl. tax') }}:</td>
                        <td class="totals-value">{{ number_format($totalHt, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="totals-label">{{ $taxLabel }} ({{ $gstPercent }}%):</td>
                        <td class="totals-value">{{ number_format($totalGst, 2) }}</td>
                    </tr>
                    <tr class="grand-total-row">
                        <td class="totals-label">{{ __('Total incl. tax') }} ({{ $currencyCode }}) :</td>
                        <td class="totals-value">{{ number_format($totalTtc, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer-section">
        <div class="services-bar">
            {{ __('Buy') }} <span>|</span> {{ __('Sell') }} <span>|</span> {{ __('Repair') }} <span>|</span> {{ __('IT services') }} <span>|</span> {{ __('Exchange & trade-in') }}
        </div>
    </div>

</body>
</html>
