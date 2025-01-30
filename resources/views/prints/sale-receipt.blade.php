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
            padding: 10px 5px;
        }
        .company-info {
            text-align: center;
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
            font-size: 8px;
            margin-top: 30px;
            color: #888;
        }

        .footer p {
            margin: 5px 0;
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
    <div class="company-info">
        <h1>{{ $shop->name }}</h1>
        <p>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }} </p>
        <p>{{ $shop->state }}, {{ $shop->country }} </p>
        <p style="padding-bottom: 0.5rem;">Phone: {{ $shop->contact_no }} | Email: {{ $shop->email }}</p>
    </div>

    <div class="receipt-header">
        <h1>Sales Receipt</h1>
        <p>Receipt Number: #{{ $sale->id }}</p>
        <p>Date: {{ $sale->created_at->format('F j, Y') }}</p>
        <p>Customer: {{ $sale->customer?->name }}</p>
    </div>

    <table class="receipt-details" border="1" cellpadding="1" cellspacing="0">
        <thead>
            <tr>
                <th style="width:50%;">Product</th>
                <th class="text-center">Unit Price</th>
                <th class="text-center">Quantity</th>
                <th class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
                <tr>
                    <td>
                        {{ $item->product?->name }}
                        @if (collect($item->attributes_and_values)->filter(fn($p) => !empty($p['name']))->count())
                        <br>
                        <table style="width:20%; margin:0; padding:0;">
                            @foreach ($item->attributes_and_values ?? [] as $att_value)
                            <tr>
                                <td style="padding:0 1px;"> <small class="text-muted">{{ ucfirst($att_value['name']) }}:</small> </td>
                                <td style="padding:0 1px;"> <small> {{ implode(', ', $att_value['values']) }}</small></td>
                            </tr>
                            @endforeach
                        </table>
                        @endif
                    </td>
                    <td class="text-end">{{ $item->price }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ $item->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Breakdown -->
    <table class="totals-table" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <th class="left">Subtotal</td>
            <td class="right"> {{ number_format($sale->saleItems->sum('total'), 2) }} </td>
        </tr>
        <tr>
            <th class="left">Discount</td>
            <td class="right">- {{ $sale->discount }}</td>
        </tr>
        <tr>
            <th class="left">GST ({{ $shop->gst_percent }}%)</td>
            <td class="right">{{ $sale->gst }}</td>
        </tr>
        <tr>
            <th class="left">Grand Total <small>({{  $shop->currency['code'] }})</small></td>
            <td class="right total-amount">{{ $sale->total_price }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Thank you for your purchase!</p>
    </div>
</body>
</html>
