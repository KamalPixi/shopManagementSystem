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
        <p>Phone: {{ $shop->contact_no }} | Email: {{ $shop->email }}</p>
    </div>

    <div class="receipt-header">
        <h1>Sales Receipt</h1>
        <p>Receipt Number: #{{ $sale->id }}</p>
        <p>Customer: {{ $sale->customer?->name }} @if($sale->customer?->phone_no) | {{ $sale->customer?->phone_no }} @endif</p>
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
                        <b>{{ $item->product?->name }}</b> <br>
                        @if ($item?->product->serial_no)
                            <small class="text-muted">SN:{{ $item?->product->serial_no }}</small> <br>
                        @endif
                        <small class="text-muted">Model: {{ $item?->product->model_no }} | SKU: {{ $item?->product->sku }}</small><br>
                        @foreach ($item->attributes_and_values ?? [] as $att => $value)
                        <small style="font-size: 5px;">
                           {{ ucfirst($att) }}:{{ ucfirst($value) }}
                        </small>
                        @endforeach
                    </td>
                    <td class="text-end">{{ $item->price }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ $item->total }}</td>
                </tr>
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
            <td class="right">- {{ $sale->discount }}</td>
        </tr>
        <tr>
            <th class="left">GST ({{ $shop->gst_percent }}%):</td>
            <td class="right">{{ $sale->gst }}</td>
        </tr>
        <tr>
            <th class="left">Total: <small>({{  $shop->currency['code'] }})</small></td>
            <td class="right total-amount">{{ $sale->total_price }}</td>
        </tr>
    </table>

    <div class="footer">
        <p style="margin-bottom: 10px;color: #888;">Thank you for your purchase!</p>
        <p>For any enquiry, Please call us</p>
        <p>{{ $shop->contact_no }}</p>
        <p>{{ $sale->created_at->format('F j, Y g:i A') }}</p>
    </div>
</body>
</html>
