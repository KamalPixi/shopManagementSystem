<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Receipt</title>
    <style>
        html {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
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

        .shop-logo {
            max-width: 110px;
            max-height: 48px;
            height: auto;
            width: auto;
            display: block;
            margin: 0 auto 5px auto;
            object-fit: contain;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php
        $gstPercent = (float) ($shop?->gst_percent ?? 0);
        $hasVatItems = $sale->saleItems->contains(fn ($item) => (bool) $item->is_vatable && $gstPercent > 0);
        $hasNonVatItems = $sale->saleItems->contains(fn ($item) => !(bool) $item->is_vatable);
        $taxLabel = $shop?->tax_label ?: '-';
    @endphp
    <div class="company-info">
        @if (!empty($shop->logo) && file_exists(public_path($shop->logo)))
            <img src="{{ public_path($shop->logo) }}" alt="{{ $shop->name }}" class="shop-logo">
        @endif
        <h1>{{ $shop->name }}</h1>
        <p>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }} </p>
        <p>{{ $shop->state }}, {{ $shop->country }} </p>
        <p>Phone: {{ $shop->contact_no }} | Email: {{ $shop->email }}</p>
    </div>

    <div class="receipt-header">
        <h1>Sales Receipt</h1>
        <p>Receipt Number: <b>{{ $sale->receipt_no }}</b></p>
        <p>Date: {{ $sale->created_at->format('d/m/Y h:i A') }} | Cashier: {{ $sale->createdBy?->name ?? '-' }}</p>
        <p>Payment: {{ ucfirst(str_replace('_', ' ', $sale->payment_method ?: 'Cash')) }} @if($sale->payment_reference)| Ref: {{ $sale->payment_reference }}@endif</p>
        <p>Customer: {{ $sale->customer?->name ?: 'Walk In' }} @if($sale->customer?->phone_no) | {{ $sale->customer->phone_no }} @endif</p>
    </div>

    <table class="receipt-details" border="0" cellpadding="1" cellspacing="0">
        <thead>
            <tr>
                <th style="width:4%;"></th>
                <th style="width:50%;" class="text-start">Product</th>
                <th class="text-center">Unit Price</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td style="line-height: 1;">
                        <b>{{ $item->name }}</b>
                        @if ($item->is_vatable)
                            <small style="font-size: 6px; color: #28a745;">(VAT)</small>
                        @endif
                        <br>
                        @php
                            $sn = $item->productVariation?->serial_no ?: $item->product?->serial_no;
                        @endphp
                        @if ($sn && $item->show_serial_on_receipt)
                            <small class="text-muted">SN:{{ $sn }}</small> <br>
                        @endif
                        @if ($item->product?->sku || $item->product?->model_no)
                            <small class="text-muted">Model: {{ $item->product?->model_no }} | SKU: {{ $item->productVariation?->sku ?: $item->product?->sku }}</small><br>
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
                    <td class="text-end">{{ $item->price }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ $item->total }}</td>
                </tr>
                @php
                    $warrantyText = $item->warranty_description ?: ($item->warranty?->description ?: $item->warranty?->name);
                @endphp
                @if ($warrantyText && $item->show_warranty_on_receipt)
                <tr>
                    <td></td>
                    <td colspan="4" style="font-size: 7px; color: #555; padding-bottom: 4px; font-style: italic;">
                        &bull; Warranty: {{ $warrantyText }}
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
            <th class="left">Subtotal:</td>
            <td class="right"> {{ number_format($sale->saleItems->sum('total'), 2) }} </td>
        </tr>
        <tr>
            <th class="left">Discount:</td>
            <td class="right">{{ $sale->discount }}</td>
        </tr>
        @if($hasVatItems)
        <tr>
            <th class="left">{{ $taxLabel }} ({{ $gstPercent }}%):</td>
            <td class="right">{{ $sale->gst }}</td>
        </tr>
        @endif
        <tr>
            <th class="left">Total: <small>({{  $shop->currency['code'] }})</small></td>
            <td class="right total-amount">{{ $sale->total_price }}</td>
        </tr>
    </table>

    @if($hasNonVatItems)
        <div style="margin-top: 8px; padding: 6px; background: #f2f5f8; font-size: 6px; font-weight: normal; line-height: 1.25;">
            {{ __('Special scheme - Second-hand goods') }}
        </div>
    @endif

    <div class="footer">
        <p style="margin-bottom: 10px;color: #888;">Thank you for your purchase!</p>
        <p>For any enquiry, Please call us</p>
        <p>{{ $shop->contact_no }}</p>
        <p>{{ $sale->created_at->format('F j, Y g:i A') }}</p>
    </div>
</body>
</html>
