<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Thermal Sales Receipt') }}</title>
    <style>
        html {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 10px;
            padding: 20px 5px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 10px;
        }
        .company-info h1 {
            margin: 0;
            font-size: 10px;
        }
        .company-info p {
            margin: 0px;
            font-size: 8px;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 15px;
        }

        .receipt-header h1 {
            margin: 0;
            font-size: 10px;
        }

        .receipt-header p {
            font-size: 8px;
            margin: 0;
            padding: 0;
        }

        .receipt-details {
            width: 100%;
            margin-bottom: 30px;
        }

        .receipt-details th, .receipt-details td {
            font-size: 8px;
        }

        .receipt-details th {
            background-color: #f2f2f2;
        }

        .totals-table {
            width: 100%;
        }

        .totals-table tr th {
            text-align: left;
            font-size: 8px !important;
        }

        .totals-table .right {
            text-align: right;
        }

        .total-amount {
            font-size: 11px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: 7px;
            margin-top: 30px;
        }

        .footer p {
            margin: 0 0;
        }
        .text-start {
            text-align: left !important;
        }
        .text-center {
            text-align: center !important;
        }
        .text-end {
            text-align: right !important;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php
        $receiptRows = \App\Services\SaleReceiptAmounts::forSale($sale);
        $gstPercent = (float) ($shop?->gst_percent ?? 0);
        $usesInclusivePrices = (float) $sale->gst > 0 && abs((float) $sale->total_price - $sale->saleItems->sum(fn ($item) => (float) $item->total)) < 0.02;
        $hasVatItems = $sale->saleItems->contains(fn ($item) => (bool) $item->is_vatable && $gstPercent > 0);
        $hasNonVatItems = $sale->saleItems->contains(fn ($item) => !(bool) $item->is_vatable);
        $configuredTaxLabel = $shop?->tax_label ?: 'VAT';
        $taxLabel = $usesInclusivePrices && in_array(strtoupper(trim($configuredTaxLabel)), ['VAT', 'TVA'], true)
            ? __('Included VAT')
            : $configuredTaxLabel;
        $locale = app()->getLocale();
        $shopTimezone = $shop?->timezone ?? 'Europe/Paris';
        $paymentMethod = match (strtolower($sale->payment_method ?: 'cash')) {
            'cash' => __('Cash'),
            'card' => __('Card'),
            'bank_transfer' => __('Bank transfer'),
            'mobile_payment' => __('Mobile payment'),
            'other' => __('Other'),
            default => ucfirst(str_replace('_', ' ', $sale->payment_method)),
        };
    @endphp
    <div class="company-info">
        <h1>{{ $shop->name }}</h1>
        <p>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }} </p>
        <p>{{ $shop->state }}, {{ $shop->country }} </p>
        <p>{{ __('Tel:') }} {{ $shop->contact_no }} | {{ __('Email') }}: {{ $shop->email }}</p>
    </div>

    <div class="receipt-header">
        <h1>{{ __('Thermal Sales Receipt') }}</h1>
        <p>{{ __('Thermal Receipt Number') }}: <b>{{ $sale->receipt_no }}</b></p>
        <p>{{ __('Date') }}: {{ $sale->created_at->copy()->timezone($shopTimezone)->format('d/m/Y H:i') }} | {{ __('Cashier') }}: {{ $sale->createdBy?->name ?? '-' }}</p>
        <p>{{ __('Thermal Payment Method') }}: {{ $paymentMethod }} @if($sale->payment_reference)| {{ __('Reference') }}: {{ $sale->payment_reference }}@endif</p>
        <p>{{ __('Customer') }}: {{ $sale->customer?->name ?: __('Walk In') }} @if($sale->customer?->phone_no) | {{ $sale->customer->phone_no }} @endif</p>
    </div>

    <table class="receipt-details" border="0" cellpadding="1" cellspacing="0">
        <thead>
            <tr>
                <th style="width:4%;"></th>
                <th style="width:40%;" class="text-start">{{ __('Product') }}</th>
                <th class="text-center">{{ __('Thermal Unit Price excl. tax') }}</th>
                <th class="text-center">{{ __('Qty') }}</th>
                @if($hasVatItems)<th class="text-center">{{ __('VAT') }}</th>@endif
                <th class="text-center">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td style="line-height: 1;">
                        <b>{{ $item->name }}</b>
                        @if ($item->is_vatable)
                            <small style="font-size: 6px; color: #28a745;">({{ __('VAT') }})</small>
                        @endif
                        <br>
                        @php
                            $sn = $item->productVariation?->serial_no ?: $item->product?->serial_no;
                        @endphp
                        @if ($sn && $item->show_serial_on_receipt)
                            <small class="text-muted">{{ __('Serial No.') }}:{{ $sn }}</small> <br>
                        @endif
                        @if ($item->product?->sku || $item->product?->model_no)
                            <small class="text-muted">{{ __('Model') }}: {{ $item->product?->model_no }} | SKU: {{ $item->productVariation?->sku ?: $item->product?->sku }}</small><br>
                        @endif
                        @foreach ($item->attributes_and_values ?? [] as $att => $value)
                        @php
$attributeName = is_array($value) ? ($value['name'] ?? '') : $att
@endphp
                        @php
$attributeValue = is_array($value) ? ($value['value'] ?? '') : $value
@endphp
                        <small style="font-size: 5px;">
                           {{ ucfirst($attributeName) }}:{{ ucfirst($attributeValue) }}
                        </small>
                        @endforeach
                    </td>
                    <td class="text-end">{{ number_format($receiptRows[$loop->index]['unit_net'], 2) }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    @if($hasVatItems)<td class="text-end">{{ number_format($receiptRows[$loop->index]['vat'], 2) }}</td>@endif
                    <td class="text-end">{{ number_format($receiptRows[$loop->index]['total'], 2) }}</td>
                </tr>
                @php
                    $warrantyText = $item->warranty_description ?: ($item->warranty?->description ?: $item->warranty?->name);
                @endphp
                @if ($warrantyText && $item->show_warranty_on_receipt)
                <tr>
                    <td></td>
                    <td colspan="{{ $hasVatItems ? 5 : 4 }}" style="font-size: 7px; color: #555; padding-bottom: 4px; font-style: italic;">
                        &bull; {{ __('Warranty') }}: {{ $warrantyText }}
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <hr>
    <!-- Totals Breakdown -->
    <table class="totals-table" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <th class="left">{{ __('Subtotal excl. tax') }}:</td>
            <td class="right"> {{ number_format($sale->saleItems->sum(fn ($item) => (float) $item->price * (int) $item->quantity) - ($usesInclusivePrices ? (float) $sale->gst : 0), 2) }} </td>
        </tr>
        <tr>
            <th class="left">{{ __('Discount') }}:</td>
            <td class="right">{{ $sale->discount }}</td>
        </tr>
        @if($hasVatItems)
        <tr>
            <th class="left">{{ $taxLabel }} ({{ $gstPercent }}%):</td>
            <td class="right">{{ $sale->gst }}</td>
        </tr>
        @endif
        <tr>
            <th class="left">{{ __('Total incl. tax') }}: <small>({{  $shop->currency['code'] }})</small></td>
            <td class="right total-amount">{{ $sale->total_price }}</td>
        </tr>
    </table>

    @if($hasNonVatItems)
        <div style="margin-top: 8px; padding: 6px; background: #f2f5f8; font-size: 6px; font-weight: normal; line-height: 1.25;">
            {{ __('Special scheme - Second-hand goods') }}
        </div>
    @endif

    <div class="footer">
        <p style="margin-bottom: 10px;color: #888;">{{ __('Thank you for your purchase!') }}</p>
        <p>{{ __('For any inquiry, please call us') }}</p>
        <p>{{ $shop->contact_no }}</p>
        <p>{{ $sale->created_at->copy()->timezone($shopTimezone)->locale($locale)->translatedFormat($locale === 'fr' ? 'd/m/Y H:i' : 'F j, Y g:i A') }}</p>
    </div>
</body>
</html>
