<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Report</title>

    <style>
        html {
            margin: 10px 5px;
            padding: 0;
        }
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h1, h2 { text-align: center; }
        .text-center { text-align: center !important; }
        .text-end { text-align: right; }
        .text-muted { color: #6c757d; }
        .fw-bold { font-weight: bold; }
        .border { border: 1px solid #dee2e6; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #dee2e6; padding: 5px; text-align: left; }
        .table th { background-color: #f8f9fa; }
        .table-bordered { border: 1px solid #dee2e6; }
        .w-100 { width: 100%; }
        .text-end {text-align: right !important;}
        .text-start {text-align: left !important;}
        
        /* Reduced line height for company details */
        .company-info p { 
            margin: 2px 0; 
            line-height: 1.2; 
        }
        .table-product tr td {
            font-size: 8px;
            padding: 0;
        }
        .shop-logo {
            max-width: 140px;
            max-height: 60px;
            height: auto;
            width: auto;
            display: block;
            margin: 0 auto 8px auto;
            object-fit: contain;
        }
    </style>

</head>
<body>

    <!-- Company Info -->
    <div class="text-center company-info">
        @if (!empty($shop->logo) && file_exists(public_path($shop->logo)))
            <img src="{{ public_path($shop->logo) }}" alt="{{ $shop->name }}" class="shop-logo">
        @endif
        <h1 class="fw-bold" style="margin-bottom: 2px;">{{ $shop->name }}</h1>
        <p>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }}</p>
        <p>{{ $shop->state }}, {{ $shop->country }}</p>
        <p>Phone: {{ $shop->contact_no }} | Email: {{ $shop->email }}</p>
        <p>
            <strong>Report Date:</strong> <small>{{ $report_date ?: '-' }} @if($report_date) ({{ $report_date_filter_type }}) @endif</small> | 
            <strong>Print Date:</strong> <small>{{ $report_print_date }}</small>
            @if(!empty($selected_staff))
                | <strong>Staff:</strong> <small>{{ $selected_staff }}</small>
            @endif
            @if(!empty($receipt_filter))
                | <strong>Receipt Filter:</strong> <small>{{ $receipt_filter }}</small>
            @endif
        </p>
    </div>

    <!-- Report Title -->
    <h2 class="fw-bold" style="margin-bottom: 20px;">{{ __('Sales Report') }}</h2>

    <!-- Sales Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width:3%;" class="text-center">#</th>
                <th class="text-start" style="width:72%">{{ __('Products') }}</th>
                <th class="text-end" style="width:7%">{{ __('Discount') }}</th>
                <th class="text-end" style="width:7%">{{ __('GST') }}<small class="text-muted" style="font-size: 0.4rem;">({{ $shop->gst_percent }}%)</small> </th>
                <th class="text-end" style="width:7%">{{ __('Total') }}</th>
                <th class="text-end" style="width:7%">{{ __('Profit') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
            <tr>
                <td class="text-center">{{ $loop->index + 1 }}</td>
                <td>
                    <div style="background-color: #f1f3f5; padding: 2px 4px; margin-bottom: 3px; font-size: 8px;">
                        <strong>Receipt:</strong> {{ $sale->receipt_no ?? sprintf('TP-%06d', $sale->id) }} &nbsp;|&nbsp;
                        <strong>Cashier:</strong> {{ $sale->createdBy?->name ?? 'Staff' }} &nbsp;|&nbsp;
                        <strong>Payment:</strong> {{ $sale->payment_method === 'other' ? 'Other' : ucfirst(str_replace('_', ' ', $sale->payment_method ?? 'Cash')) }}@if($sale->payment_reference) ({{ $sale->payment_reference }})@endif &nbsp;|&nbsp;
                        <strong>Date:</strong> {{ $sale->created_at?->format('d/m/Y H:i') }}
                    </div>
                    <table class="table-product w-100" border="0" cellpadding="1" cellspacing="0">
                        @foreach ($sale->saleItems as $item)
                        <tr>
                            <td style="width: 45%;line-height: 1;"> 
                                <small>{{ $item->name }}</small>
                                @if ($item->productVariation?->variation_name)<br><small>Variant: {{ $item->productVariation->variation_name }}</small>@endif
                                @if (empty($item->product_id))
                                    <small style="color: #0dcaf0;">(Custom)</small>
                                @endif
                                <br>
                                @php
                                    $itemSerial = $item->productVariation?->serial_no ?: $item->product?->serial_no;
                                    $itemSku = $item->productVariation?->sku ?: $item->product?->sku;
                                @endphp
                                @if ($itemSerial && $item->show_serial_on_receipt)
                                    <small class="text-muted">SN:{{ $itemSerial }}</small> <br>
                                @endif
                                @if ($itemSku || $item->product?->model_no)
                                    <small class="text-muted">@if($itemSku)SKU: {{ $itemSku }}@endif @if($item->product?->model_no) | Model: {{ $item->product->model_no }}@endif</small>
                                @endif
                            </td>
                            <td style="width: 18%;"> 
                                <table class="w-100">
                                    @foreach ($item->attributes_and_values ?? [] as $att => $value)
                                    @php
$attributeName = is_array($value) ? ($value['name'] ?? '') : $att
@endphp
                                    @php
$attributeValue = is_array($value) ? ($value['value'] ?? '') : $value
@endphp
                                    <tr>
                                        <td class="text-muted"><small>{{ ucfirst($attributeName) }}</small></td>
                                        <td><small><b>: {{ ucfirst($attributeValue) }}</b></small></td>
                                    </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td class="text-center"><small>Warranty</small><br><b>{{ $item?->warranty?->name ?? '-' }}</b></td>
                            <td class="text-center"><small>Qty</small><br><b>{{ $item?->quantity }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>Unit Purchase Price</small><br><b>{{ number_format((float) $item->purchase_price, 2) }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>Unit Sale Price</small><br><b>{{ number_format((float) $item->price, 2) }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>Discount</small><br><b>{{ number_format((float) $item->discount, 2) }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>Subtotal</small><br><b>{{ number_format((float) $item->total, 2) }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>Profit</small><br><b>{{ number_format((float) $item->profit, 2) }}</b></td>
                        </tr>
                        @endforeach
                    </table>
                </td>
                <td class="text-end">{{ number_format((float) $sale->discount, 2) }}</td>
                <td class="text-end">{{ number_format((float) $sale->gst, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format((float) $sale->total_price, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format((float) $sale->profit, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Table -->
    <hr>
    <table class="table">
        <thead>
            <tr>
                <th style="width:5%;"></th>
                <th class="text-start" style="width:72%;"></th>
                <th class="text-end"> <small class="text-muted">T.Discount</small> {{ number_format($sales->sum('discount'), 2) }}</th>
                <th class="text-end"><small class="text-muted">T.GST</small>  {{ number_format($sales->sum('gst'), 2) }}</th>
                <th class="text-end"><small class="text-muted">T.Price</small>  {{ number_format($sales->sum('total_price'), 2) }}</th>
                <th class="text-end"><small class="text-muted">T.Profit</small> {{ number_format($sales->sum('profit'), 2) }}</th>
            </tr>
        </thead>
    </table>

</body>
</html>
