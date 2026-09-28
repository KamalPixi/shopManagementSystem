<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $sale->receipt_no }}</title>
    <style>
        @page {
            margin: 25px 30px 25px 30px;
            size: a4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
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
            font-size: 22px;
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
            max-height: 55px;
            max-width: 160px;
            object-fit: contain;
            margin-bottom: 4px;
        }
        .top-contact-info {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
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
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            width: 100%;
        }
        .facture-title {
            font-size: 20px;
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
            background-color: #f1f5f9;
            border-left: 4px solid #0f2c59;
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
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 7px 6px;
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
        }
        .payment-mode-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            height: 100%;
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
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }
        .signature-msg {
            font-family: 'Georgia', 'Times New Roman', serif;
            font-style: italic;
            font-size: 13px;
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
        $currencyCode = $shop->currency['code'] ?? 'EUR';
        $currencySymbol = ($currencyCode === 'EUR' || $currencyCode === '€') ? '€' : $currencyCode;
        $gstPercent = floatval($shop->gst_percent ?? 20);

        // Calculate Subtotal HT, TVA, Total TTC
        $totalTtc = floatval($sale->total_price ?? 0);
        $totalGst = floatval($sale->gst ?? 0);
        $discount = floatval($sale->discount ?? 0);
        $subtotalTtc = floatval($sale->saleItems->sum('total') ?? $totalTtc);
        $subtotalHt = 0;

        foreach ($sale->saleItems as $item) {
            $itemTotal = floatval($item->total);
            if ($item->is_vatable && $gstPercent > 0) {
                $subtotalHt += ($itemTotal / (1 + ($gstPercent / 100)));
            } else {
                $subtotalHt += $itemTotal;
            }
        }
        $totalHt = max(0, $subtotalHt - $discount);
        
        $paymentMethod = strtolower($sale->payment_method ?? 'cash');
    @endphp

    <!-- Header Table -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                @if (!empty($shop->logo) && file_exists(public_path($shop->logo)))
                    <img src="{{ public_path($shop->logo) }}" alt="{{ $shop->name }}" class="shop-logo"><br>
                @endif
                <div class="brand-name">{{ $shop->name ?? 'TECH PARISIEN' }}</div>
                <div class="brand-slogan">ACHETEZ &bull; VENDEZ &bull; ÉCHANGEZ</div>
            </td>
            <td style="width: 45%; vertical-align: middle; text-align: right;" class="top-contact-info">
                @if(!empty($shop->address))
                    <div>{{ $shop->address }}</div>
                @endif
                @if(!empty($shop->postal_code) || !empty($shop->city))
                    <div>{{ $shop->postal_code }} {{ $shop->city }}{{ !empty($shop->country) ? ', '.$shop->country : '' }}</div>
                @endif
                @if(!empty($shop->contact_no))
                    <div><strong>Tél :</strong> {{ $shop->contact_no }}</div>
                @endif
                @if(!empty($shop->email))
                    <div><strong>Email :</strong> {{ $shop->email }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Legal & Invoice Meta Section -->
    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 52%; vertical-align: top; padding-right: 15px;" class="company-legal-box">
                <div class="company-legal-title">{{ $shop->name ?? 'TECH PARISIEN' }}</div>
                <div>SAS au capital de 2 010 €</div>
                <div>{{ $shop->address ?? '33 rue Brézin' }}, {{ $shop->postal_code ?? '75014' }} {{ $shop->city ?? 'Paris' }}</div>
                <div>SIREN : 109 915 405</div>
                <div>TVA intracommunautaire : FR75 109915405</div>
            </td>
            <td style="width: 48%; vertical-align: top;">
                <div class="facture-box">
                    <div class="facture-title">FACTURE</div>
                    <div class="facture-meta-row"><strong>N° de facture :</strong> {{ $sale->receipt_no }}</div>
                    <div class="facture-meta-row"><strong>Date :</strong> {{ $sale->created_at->format('d/m/Y') }}</div>
                    <div class="facture-meta-row"><strong>Heure :</strong> {{ $sale->created_at->format('H:i') }}</div>
                    <div class="facture-meta-row"><strong>Caissier :</strong> {{ $sale->createdBy?->name ?? 'Admin' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Client Info -->
    <div class="client-card">
        <div class="client-card-title">Facturé à :</div>
        <div class="client-name">{{ $sale->customer?->name ?: 'Client au comptoir' }}</div>
        @if($sale->customer?->address || $sale->customer?->city)
            <div class="client-details">{{ $sale->customer?->address }} {{ $sale->customer?->city }}</div>
        @endif
        @if($sale->customer?->phone_no || $sale->customer?->email)
            <div class="client-details">
                @if($sale->customer?->phone_no) Tél : {{ $sale->customer->phone_no }} @endif
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
                <th style="width: 44%; text-align: left;">Désignation du produit</th>
                <th style="width: 14%; text-align: right;">Prix unit. HT</th>
                <th style="width: 8%; text-align: center;">TVA</th>
                <th style="width: 14%; text-align: right;">Prix unit. TTC</th>
                <th style="width: 4%; text-align: center;">Qté</th>
                <th style="width: 12%; text-align: right;">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $index => $item)
                @php
                    $isVat = $item->is_vatable;
                    $itemVatRate = $isVat ? $gstPercent : 0;
                    $unitTtc = floatval($item->price);
                    $unitHt = $isVat && $gstPercent > 0 ? ($unitTtc / (1 + ($gstPercent / 100))) : $unitTtc;
                    $totalLineTtc = floatval($item->total);
                    $warrantyText = $item->warranty_description ?: ($item->warranty?->description ?: $item->warranty?->name);
                    $sn = $item->productVariation?->serial_no ?: $item->product?->serial_no;
                @endphp
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                    <td>
                        <div class="product-title">
                            {{ $item->name }}
                            @if($isVat)
                                <span class="badge-vat">TVA {{ $itemVatRate }}%</span>
                            @endif
                        </div>
                        <div class="product-meta">
                            @if ($item->productVariation?->variation_name)
                                <span><b>Variante :</b> {{ $item->productVariation->variation_name }}</span><br>
                            @endif
                            @if ($item->productVariation?->sku || $item->product?->sku)
                                <span><b>SKU :</b> {{ $item->productVariation?->sku ?: $item->product?->sku }}</span>
                            @endif
                            @if ($item->product?->model_no)
                                <span> &bull; <b>Modèle :</b> {{ $item->product?->model_no }}</span>
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
                                <br><span style="color: #0f2c59;"><b>Garantie :</b> {{ $warrantyText }}</span>
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
                    <div class="payment-mode-title">Mode de règlement</div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['cash', 'especes', 'espèce', 'espece']) ? 'checked' : '' }}">
                            {!! in_array($paymentMethod, ['cash', 'especes', 'espèce', 'espece']) ? '&#10003;' : '&nbsp;' !!}
                        </span>
                        Espèces (Cash)
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['card', 'carte', 'cb', 'credit_card', 'carte bancaire']) ? 'checked' : '' }}">
                            {!! in_array($paymentMethod, ['card', 'carte', 'cb', 'credit_card', 'carte bancaire']) ? '&#10003;' : '&nbsp;' !!}
                        </span>
                        Carte bancaire (CB)
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['bank_transfer', 'virement', 'transfer']) ? 'checked' : '' }}">
                            {!! in_array($paymentMethod, ['bank_transfer', 'virement', 'transfer']) ? '&#10003;' : '&nbsp;' !!}
                        </span>
                        Virement bancaire
                    </div>
                    <div class="payment-option">
                        <span class="checkbox-box {{ in_array($paymentMethod, ['cheque', 'check', 'other', 'autre']) ? 'checked' : '' }}">
                            {!! in_array($paymentMethod, ['cheque', 'check', 'other', 'autre']) ? '&#10003;' : '&nbsp;' !!}
                        </span>
                        Autre / Chèque
                    </div>
                    @if($sale->payment_reference)
                        <div style="font-size: 8.5px; color: #64748b; margin-top: 6px;">
                            <strong>Réf. :</strong> {{ $sale->payment_reference }}
                        </div>
                    @endif
                </div>
            </td>

            <!-- Totals Breakdown -->
            <td style="width: 50%; vertical-align: top;">
                <table class="totals-card">
                    <tr>
                        <td class="totals-label">Sous-total HT :</td>
                        <td class="totals-value">{{ number_format($subtotalHt, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @if($discount > 0)
                    <tr>
                        <td class="totals-label" style="color: #e11d48;">Remise :</td>
                        <td class="totals-value" style="color: #e11d48;">- {{ number_format($discount, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="totals-label">Total HT :</td>
                        <td class="totals-value">{{ number_format($totalHt, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    <tr>
                        <td class="totals-label">TVA ({{ $gstPercent }}%) :</td>
                        <td class="totals-value">{{ number_format($totalGst, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                    <tr class="grand-total-row">
                        <td class="totals-label">Total TTC (EUR) :</td>
                        <td class="totals-value">{{ number_format($totalTtc, 2) }} {{ $currencySymbol }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer-section">
        <div class="signature-msg">Merci pour votre confiance !</div>
        <div class="signature-team">L'équipe {{ $shop->name ?? 'Tech Parisien' }}</div>
        
        <div class="services-bar">
            Achat <span>|</span> Vente <span>|</span> Réparation <span>|</span> Services informatiques <span>|</span> Échange & Reprise
        </div>
        <div class="system-note">
            Document généré électroniquement &bull; TECH PARISIEN SAS &bull; Tous droits réservés
        </div>
    </div>

</body>
</html>

