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
            font-size: 12px;
            padding: 40px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 11px;
        }
        .company-info h1 {
            margin: 0;
            font-size: 14px;
        }
        .company-info p {
            margin: 0px;
            font-size: 10px;
        }

        .receipt-details {
            width: 100%;
            margin-bottom: 30px;
        }

        .receipt-details th, .receipt-details td {
            font-size: 10px;
        }

        .receipt-details th {
            background-color: #f2f2f2;
        }

        .totals-table {
            width: 100%;
        }

        .totals-table tr th {
            text-align: left;
            font-size: 10px !important;
        }

        .totals-table .right {
            text-align: right;
        }

        .total-amount {
            font-size: 11px;
            font-weight: bold;
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
        .w-100 {
            width: 100%;
        }
        .customer {
            margin-bottom: 20px;
        }
        .customer td {
            padding: 0;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="company-info">
        <h1>{{ $shop->name }}</h1>
        <p>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }} </p>
        <p>{{ $shop->state }}, {{ $shop->country }} </p>
        <p>Phone: {{ $shop->contact_no }} <b style="color: #585858">|</b> Email: {{ $shop->email }}</p>
    </div>

    <table class="w-100 customer">
        <tr>
            <td style="width: 10%;">To</td>
            <td style="width: 70%;">:  {{ $sale->customer?->name }}</td>
            <td style="width: 10%;">Receipt No.</td>
            <td>: #{{ $sale->id }}</td>
        </tr>
        <tr>
            <td style="width: 10%;">Mobile No.</td>
            <td style="width: 70%;">:  {{ $sale->customer?->phone_no }}</td>
            <td style="width: 10%;">Date</td>
            <td>: {{ $sale->created_at->format('Y-m-d') }}</td>
        </tr>
    </table>

    <hr>

    <table class="receipt-details" border="0" cellpadding="1" cellspacing="0">
        <thead>
            <tr>
                <th style="width:4%;"></th>
                <th style="width:50%;" class="text-start">Products</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Quantity</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td style="line-height: 1;">
                        {{ $item->name }} <br>
                        @if ($item->product?->model_no)
                            <small style="font-size: 8px;">{{ $item->product?->model_no }}</small> 
                        @endif
                        @if ($item->product?->serial_no)
                            <small style="font-size: 8px;">{{ $item->product?->serial_no }}</small> <br>
                        @endif
                        @foreach ($item->attributes_and_values ?? [] as $att => $value)
                        <small style="font-size: 8px;">
                           {{ ucfirst($att) }}:{{ ucfirst($value) }}
                        </small>
                        @endforeach
                    </td>
                    <td class="text-end">{{ $item->price }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">{{ $item->total }}</td>
                </tr>
                @php
                    $warrantyText = $item->warranty_description ?: ($item->warranty?->description ?: $item->warranty?->name);
                @endphp
                @if ($warrantyText)
                <tr>
                    <td></td>
                    <td colspan="4" style="font-size: 8px; color: #4b5563; padding-bottom: 5px; font-style: italic;">
                        <strong>Warranty:</strong> {{ $warrantyText }}
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <hr>

    
    <table class="totals-table" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <th class="left">Subtotal:</td>
            <td class="right"> {{ number_format($sale->saleItems->sum('total'), 2) }} </td>
        </tr>
        <tr>
            <th class="left">Discount:</td>
            <td class="right">- {{ $sale->discount }}</td>
        </tr>
        <tr>
            <th class="left">{{ $shop->tax_label ?? 'GST' }} ({{ $shop->gst_percent }}%):</td>
            <td class="right">{{ $sale->gst }}</td>
        </tr>
        <tr>
            <th class="left">Total: <small>({{  $shop->currency['code'] }})</small></td>
            <td class="right total-amount">{{ $sale->total_price }}</td>
        </tr>
    </table>

    <div>
        <p style="color: #b4b4b4;font-size: 8px;">This is a system-generated document; no signature is required.</p>
    </div>

</body>
</html>
