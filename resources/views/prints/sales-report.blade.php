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
    </style>

</head>
<body>

    <!-- Company Info -->
    <div class="text-center company-info">
        <h1 class="fw-bold" style="margin-bottom: 2px;">{{ $shop->name }}</h1>
        <p>{{ $shop->address }}, {{ $shop->city }} {{ $shop->postal_code }}</p>
        <p>{{ $shop->state }}, {{ $shop->country }}</p>
        <p>Phone: {{ $shop->contact_no }} | Email: {{ $shop->email }}</p>
        <p>
            <strong>Report Date:</strong> <small>{{ $report_date ?: '-' }} @if($report_date) ({{ $report_date_filter_type }}) @endif</small> | 
            <strong>Print Date:</strong> <small>{{ $report_print_date }}</small>
        </p>
    </div>

    <!-- Report Title -->
    <h2 class="fw-bold" style="margin-bottom: 20px;">Sales Report</h2>

    <!-- Sales Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width:3%;" class="text-center">#</th>
                <th class="text-start" style="width:65%">Products</th>
                <th class="text-end">Discount</th>
                <th class="text-end">GST<small class="text-muted" style="font-size: 0.4rem;">({{ $shop->gst_percent }}%)</small> </th>
                <th class="text-end">Total</th>
                <th class="text-end">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $sale)
            <tr>
                <td class="text-center">{{ $loop->index + 1 }}</td>
                <td>
                    <table class="table-product w-100" border="0" cellpadding="1" cellspacing="0">
                        @foreach ($sale->saleItems as $item)
                        <tr>
                            <td style="width: 45%;line-height: 1;"> 
                                <small>{{ $item?->product->name }}</small><br>
                                @if ($item?->product->serial_no)
                                    <small class="text-muted">SN:{{ $item?->product->serial_no }}</small> <br>
                                @endif
                                <small class="text-muted">SKU: {{ $item?->product->sku }} | Model: {{ $item?->product->model_no }}</small>
                            </td>
                            <td style="width: 18%;"> 
                                <table class="w-100">
                                    @foreach ($item->attributes_and_values ?? [] as $att => $value)
                                    <tr>
                                        <td class="text-muted"><small>{{ ucfirst($att) }}</small></td>
                                        <td><small><b>: {{ ucfirst($value) }}</b></small></td>
                                    </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td class="text-center"><small>Warranty</small><br><b>{{ $item?->warranty?->name ?? '-' }}</b></td>
                            <td class="text-center"><small>Qty</small><br><b>{{ $item?->quantity }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>UnitPurchasePrice</small><br><b>{{ $item?->purchase_price }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>UnitSalePrice</small><br><b>{{ $item->price }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>Discount</small><br><b>{{ $item?->discount }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>Subtotal</small><br><b>{{ $item?->total }}</b></td>
                            <td class="text-end" style="padding-right:1px;"><small>Profit</small><br><b>{{ $item?->profit }}</b></td>
                        </tr>
                        @endforeach
                    </table>
                </td>
                <td class="text-end">{{ $sale->discount }}</td>
                <td class="text-end">{{ $sale->gst }}</td>
                <td class="text-end fw-bold">{{ $sale->total_price }}</td>
                <td class="text-end fw-bold">{{ $sale->profit }}</td>
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
                <th class="text-start" style="width:65%;"></th>
                <th class="text-end"> <small class="text-muted">T.Discount</small> {{ number_format($sales->sum('discount'), 2) }}</th>
                <th class="text-end"><small class="text-muted">T.GST</small>  {{ number_format($sales->sum('gst'), 2) }}</th>
                <th class="text-end"><small class="text-muted">T.Price</small>  {{ number_format($sales->sum('total_price'), 2) }}</th>
                <th class="text-end"><small class="text-muted">T.Profit</small> {{ number_format($sales->sum('profit'), 2) }}</th>
            </tr>
        </thead>
    </table>

</body>
</html>
