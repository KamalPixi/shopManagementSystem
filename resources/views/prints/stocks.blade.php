<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Stock Report</title>
    <style>
        html {
            margin: 10px;
            padding: 10px;
        }
        body {
            font-family: Arial, sans-serif;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-info h1 {
            margin: 0;
            font-size: 12px;
        }
        .company-info p {
            margin: 0px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 2px 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .print_date {
            font-size: 8px;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .lh-1 {
            line-height: 1;
        }
    </style>
</head>
<body>

<div class="company-info">
    <h1>{{ $shop->name }}</h1>
    <p>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }}, {{ $shop->state }}, {{ $shop->country }} </p>
    <p style="padding-bottom: 0.5rem;">Phone: {{ $shop->contact_no }} | Email: {{ $shop->email }}</p>
    <p class="print_date"> <strong class="print_date">{{ __('Stock Date') }}:</strong> <small>{{  $stock_date ? $stock_date : '(All)' }} @if($stock_date) &#40;{{ $stock_date_filter_type }}&#41; @endif </small> | <strong class="print_date">{{ __('Print Date') }}:</strong> <small>{{ $stock_print_date }}</small> </p>
</div>

<div class="">
    <table>
        <thead>
            <tr>
                <th style="width:2%;">#</th>
                <th>{{ __('Product') }}</th>
                <th style="width:9%;" class="text-center">{{ __('Unit Price') }}</th>
                <th style="width:9%;" class="text-center">{{ __('Stock Qty') }}</th>
                <th style="width:9%;" class="text-center">{{ __('P.Price') }}</th>
                <th style="width:9%;" class="text-center">{{ __('Total Price') }}</th>
                <th class="text-center" style="width:6%;">{{ __('Warranty') }}</th>
                <th class="text-center" style="width:8%;">{{ __('Supplier') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr>
                <td>{{ $loop->index + 1 }} </td>
                <td class="lh-1">
                    <b> {{ $product->name }} </b>
                    @if ($product->serial_no)
                        <br>
                        <small class="text-muted">SN:{{ $product->serial_no }}</small>
                    @endif
                    @if ($product->sku || $product->model_no)
                        <br>
                        <small class="text-muted">SKU: {{ $product->sku }} | Model: {{ $product->model_no }}</small>
                    @endif
    
                    @if (collect($product->attributes_and_values)->filter(fn($p) => !empty($p['name']))->count())
                    <br>
                    <table style="width:20%; margin:0; padding:0;">
                        @foreach ($product->attributes_and_values ?? [] as $att_value)
                        <tr>
                            <td style="padding:0 1px;"> <small class="text-muted">{{ ucfirst($att_value['name']) }}:</small> </td>
                            <td style="padding:0 1px;"> <small> {{ implode(', ', $att_value['values']) }}</small></td>
                        </tr>
                        @endforeach
                    </table>
                    @endif
                </td>
                <td class="text-center">{{ $product->has_variations ? 'Per variation' : $product->price }}</td>
                <td class="text-center">{{ $product->quantity }}</td>
                <td class="text-center">{{ $product->has_variations ? 'Per variation' : $product->purchase_price }}</td>
                <td class="text-center">{{ number_format($product->has_variations ? $product->variations->sum(fn($v) => $v->price * $v->quantity) : $product->price * $product->quantity, 2, '.', '') }}</td>
                <td class="text-center">{{ $product->warranty?->name ?? '-' }}</td>
                <td class="text-center">{{ $product->supplier?->name ?? '-' }}</td>
            </tr>
            @if ($product->has_variations)
                @foreach ($product->variations as $variation)
                    <tr>
                        <td></td>
                        <td>↳ {{ $variation->variation_name }}<br><small>SKU: {{ $variation->sku ?: '-' }}</small></td>
                        <td class="text-center">{{ $variation->price }}</td>
                        <td class="text-center">{{ $variation->quantity }}</td>
                        <td class="text-center">{{ $variation->purchase_price }}</td>
                        <td class="text-center">{{ number_format($variation->price * $variation->quantity, 2, '.', '') }}</td>
                        <td></td><td></td>
                    </tr>
                @endforeach
            @endif
            @endforeach
        </tbody>
    </table>
    
    <table>
        <tr>
            <td style="width:2%;"></td>
            <td></td>
            <td style="width:9%;" class="fw-bold text-center">Total Unit Price: <br> {{ number_format($products->sum('price'), 2, '.', '') }} </td>
            <td style="width:9%;" class="fw-bold text-center">Total Qty: <br> {{ $products->sum('quantity') }} </td>
            <td style="width:9%;" class="fw-bold text-center">Total Purchase Price: <br> {{ number_format($products->sum(fn($p) => $p->has_variations ? $p->variations->sum(fn($v) => $v->purchase_price * $v->quantity) : $p->purchase_price * $p->quantity), 2, '.', '') }} </td>
            <td style="width:9%;" class="fw-bold text-center">Total Sale Price: <br> {{ number_format($products->sum(fn($p) => $p->has_variations ? $p->variations->sum(fn($v) => $v->price * $v->quantity) : $p->price * $p->quantity), 2, '.', '') }} </td>
            <td style="width:6%;"></td>
            <td style="width:8%;"></td>
        </tr>
    </table>
</div>



</body>
</html>
