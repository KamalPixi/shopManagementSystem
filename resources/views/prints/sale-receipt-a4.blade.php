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
            text-transform: uppercase;
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
            text-align: left;
            padding-left: 18px;
        }
        .contact-panel {
            border-left: 1px solid #0f2c59;
            padding-left: 18px;
            padding-bottom: 6px;
        }
        .contact-row {
            display: table;
            width: 100%;
            margin: 4px 0;
        }
        .contact-icon {
            display: table-cell;
            width: 22px;
            height: 22px;
            max-width: 22px;
            max-height: 22px;
            vertical-align: middle;
            text-align: center;
            object-fit: contain;
        }
        .contact-value {
            display: table-cell;
            vertical-align: middle;
            padding-left: 8px;
            text-align: left;
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
        }
        .company-legal-title {
            font-weight: bold;
            font-size: 11px;
            color: #0f2c59;
            margin-bottom: 2px;
        }
        .facture-box {
            background-color: #eef2f7;
            border-radius: 5px;
            padding: 10px 14px;
            width: 100%;
        }
        .facture-title {
            font-size: 27px;
            font-weight: 900;
            color: #0f2c59;
            letter-spacing: 1px;
            margin: 0 0 6px 0;
            text-align: right;
        }
        .facture-meta-row {
            font-size: 9.5px;
            color: #334155;
            text-align: right;
            padding: 1px 0;
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
            font-size: 9px;
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
            font-size: 10px;
            font-weight: bold;
            color: #0f2c59;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
        }
        .payment-option {
            font-size: 9.5px;
            color: #334155;
            margin-bottom: 4px;
        }
        .checkbox-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #475569;
            text-align: center;
            line-height: 10px;
            font-size: 9px;
            font-weight: bold;
            margin-right: 5px;
            background: #fff;
        }
        .checkbox-box.checked {
            background-color: #0f2c59;
            color: #ffffff;
            border-color: #0f2c59;
        }

        /* Totals Card */
        .totals-card {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-card td {
            padding: 4px 8px;
            font-size: 9.5px;
        }
        .totals-label {
            color: #475569;
            text-align: left;
        }
        .totals-value {
            font-weight: bold;
            color: #1e293b;
            text-align: right;
        }
        .grand-total-row td {
            background-color: #0f2c59;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 12px;
            padding: 8px 10px;
        }
        .grand-total-row .totals-label {
            color: #ffffff;
        }
        .grand-total-row .totals-value {
            color: #ffffff;
        }

        /* Footer */
        .footer-section {
            margin-top: 25px;
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
            font-size: 8.5px;
            font-weight: 600;
            color: #475569;
            background-color: #f1f5f9;
            padding: 6px 10px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .services-bar span {
            color: #94a3b8;
            margin: 0 6px;
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
        $taxLabel = $shop?->tax_label ?? 'VAT';
        $currencyCode = $shop?->currency['code'] ?? 'EUR';
        $currencySymbol = $shop?->currency['symbol'] ?? $currencyCode;
        $legal = $shop?->settings['legal'] ?? [];
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
                @if (!empty($legal['company_type']))<div>{{ $legal['company_type'] }}</div>@endif
                <div>{{ collect([$shop?->address, trim(($shop?->postal_code ?? '') . ' ' . ($shop?->city ?? '')), $shop?->country])->filter()->implode(', ') }}</div>
                @if (!empty($legal['registration_number']))<div>{{ __('Registration no.') }}: {{ $legal['registration_number'] }}</div>@endif
                @if (!empty($legal['tax_number']))<div>{{ __('Tax ID') }}: {{ $legal['tax_number'] }}</div>@endif
            </td>
            <td style="width: 48%; vertical-align: top;">
                <div class="facture-box">
                    <div class="facture-title">{{ __('Invoice') }}</div>
                    <div class="facture-meta-row"><strong>{{ __('Invoice no.') }}:</strong> {{ $sale->receipt_no }}</div>
                    <div class="facture-meta-row"><strong>{{ __('Date') }}:</strong> {{ $sale->created_at->format('d/m/Y') }}</div>
                    <div class="facture-meta-row"><strong>{{ __('Time') }}:</strong> {{ $sale->created_at->format('H:i') }}</div>
                    <div class="facture-meta-row"><strong>{{ __('Sold by') }}:</strong> {{ $sale->createdBy?->name ?? '-' }}</div>
                    <div class="facture-meta-row"><strong>{{ __('Payment method') }}:</strong> {{ $paymentLabels[$paymentMethod] ?? __('Other') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Client Info -->
    <div class="client-card">
        <div class="client-card-title">{{ __('Customer') }}</div>
        <div class="client-name">{{ $sale->customer?->name ?: __('Walk-in customer') }}</div>
        @if($sale->customer?->address || $sale->customer?->city)
            <div class="client-details">{{ $sale->customer?->address }} {{ $sale->customer?->city }}</div>
        @endif
        @if($sale->customer?->phone_no || $sale->customer?->email)
            <div class="client-details">
                @if($sale->customer?->phone_no) {{ __('Phone') }}: {{ $sale->customer->phone_no }} @endif
                @if($sale->customer?->phone_no && $sale->customer?->email) | @endif
                @if($sale->customer?->email) Email : {{ $sale->customer->email }} @endif
            </div>
        @endif
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
                            @if($isVat)
                                <span class="badge-vat">{{ $taxLabel }} {{ $itemVatRate }}%</span>
                            @endif
                        </div>
                        <div class="product-meta">
                            @if ($item->productVariation?->variation_name)
                                <span><b>{{ __('Variation') }}:</b> {{ $item->productVariation->variation_name }}</span><br>
                            @endif
                            @if ($item->productVariation?->sku || $item->product?->sku)
                                <span><b>SKU :</b> {{ $item->productVariation?->sku ?: $item->product?->sku }}</span>
                            @endif
                            @if ($item->product?->model_no)
                                <span> &bull; <b>{{ __('Model') }}:</b> {{ $item->product?->model_no }}</span>
                            @endif
                            @if ($sn && $item->show_serial_on_receipt)
                                <br><span><b>SN / IMEI :</b> {{ $sn }}</span>
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
                                <br><span style="color: #0f2c59;"><b>{{ __('Warranty') }}:</b> {{ $warrantyText }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">{{ number_format($unitHt, 2) }} {{ $currencySymbol }}</td>
                    <td class="text-center">{{ $itemVatRate > 0 ? $itemVatRate.'%' : '0%' }}</td>
                    <td class="text-end">{{ number_format($unitTtc, 2) }} {{ $currencySymbol }}</td>
                    <td class="text-center font-bold">{{ $item->quantity }}</td>
                    <td class="text-end font-bold">{{ number_format($totalLineTtc, 2) }} {{ $currencySymbol }}</td>
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
                        <span class="checkbox-box {{ in_array($paymentMethod, ['cash', 'especes', 'espèce', 'espece']) ? 'checked' : '' }}">
                            {!! in_array($paymentMethod, ['cash', 'especes', 'espèce', 'espece']) ? '&#10003;' : '&nbsp;' !!}
                        </span>
                        {{ __('Cash') }}
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['card', 'carte', 'cb', 'credit_card', 'carte bancaire']) ? 'checked' : '' }}">
                            {!! in_array($paymentMethod, ['card', 'carte', 'cb', 'credit_card', 'carte bancaire']) ? '&#10003;' : '&nbsp;' !!}
                        </span>
                        {{ __('Card') }}
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['bank_transfer', 'virement', 'transfer']) ? 'checked' : '' }}">
                            {!! in_array($paymentMethod, ['bank_transfer', 'virement', 'transfer']) ? '&#10003;' : '&nbsp;' !!}
                        </span>
                        {{ __('Bank transfer') }}
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['cheque', 'check', 'other', 'autre']) ? 'checked' : '' }}">
                            {!! in_array($paymentMethod, ['cheque', 'check', 'other', 'autre']) ? '&#10003;' : '&nbsp;' !!}
                        </span>
                        {{ __('Other') }}
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
                        <td class="totals-value">{{ number_format($subtotalHt, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @if($discount > 0)
                    <tr>
                        <td class="totals-label" style="color: #e11d48;">{{ __('Discount') }}:</td>
                        <td class="totals-value" style="color: #e11d48;">- {{ number_format($discount, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="totals-label">{{ __('Total excl. tax') }}:</td>
                        <td class="totals-value">{{ number_format($totalHt, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    <tr>
                        <td class="totals-label">{{ $taxLabel }} ({{ $gstPercent }}%):</td>
                        <td class="totals-value">{{ number_format($totalGst, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    <tr class="grand-total-row">
                        <td class="totals-label">{{ __('Total incl. tax') }} ({{ $currencyCode }}):</td>
                        <td class="totals-value">{{ number_format($totalTtc, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer-section">
        <div class="signature-msg">{{ __('Thank you for your trust!') }}</div>
        <div class="signature-team">{{ __('The team at :shop', ['shop' => $shop?->name ?? 'Tech Parisien']) }}</div>
        
        <div class="services-bar">
            {{ __('Buy') }} <span>|</span> {{ __('Sell') }} <span>|</span> {{ __('Repair') }} <span>|</span> {{ __('IT services') }} <span>|</span> {{ __('Exchange & trade-in') }}
        </div>
        <div class="system-note">
            {{ __('Electronically generated document') }} &bull; {{ $shop?->name ?? 'Tech Parisien' }}
        </div>
    </div>

</body>
</html>
