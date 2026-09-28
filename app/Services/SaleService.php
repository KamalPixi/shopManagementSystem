<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sale;
use App\Models\Shop;

final class SaleService
{
    public function generateSaleReceipt($sale_id): string {  
        $sale = Sale::findOrFail($sale_id); 
        $customWidth = 3.27 * 72; // ~235.44 pts

        $shop = Shop::first();
        $numRows = $sale->saleItems()->count();
        $logoHeight = (!empty($shop?->logo) && file_exists(public_path($shop->logo))) ? 60 : 0;
        $maxHeight = ($numRows * 20) + 250 + $logoHeight;
        
        $pdf = Pdf::loadView('prints.sale-receipt', [
            'sale' => $sale,
            'shop' => $shop,
        ]);
        $pdf->setPaper([0, 0, $customWidth, $maxHeight], 'portrait');

        if (!is_dir(public_path('sales'))) {
            mkdir(public_path('sales'), 0755, true);
        }

        $file_path = public_path('sales/sale_receipt.pdf');
        $pdf->save($file_path);    
        return asset('sales/sale_receipt.pdf');
    }    

    public function generateSaleReceiptA4($sale_id): string {   
        $sale = Sale::with(['saleItems.product', 'saleItems.productVariation', 'saleItems.warranty', 'customer', 'createdBy'])->findOrFail($sale_id); 
        
        $pdf = Pdf::loadView('prints.sale-receipt-a4', [
            'sale' => $sale,
            'shop' => Shop::first(),
        ])->setPaper('a4', 'portrait')->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);
            
        if (!is_dir(public_path('sales'))) {
            mkdir(public_path('sales'), 0755, true);
        }

        $file_path = public_path('sales/sale_receipt_a4.pdf');
        $pdf->save($file_path);    
        return asset('sales/sale_receipt_a4.pdf');
    }

    public function generateSalesReport($data): string {           
        $options = [
            'default_paper_orientation' => 'portrait',
            'default_paper_size' => 'a4',
            'margin_left' => 0, // Minimal left margin in millimeters
            'margin_right' => 0 // Minimal right margin in millimeters
        ];
    
        $pdf = Pdf::loadView('prints.sales-report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions($options);

        if (!is_dir(public_path('sales'))) {
            mkdir(public_path('sales'), 0755, true);
        }

        $file_path = public_path('sales/sales_report.pdf');
        $pdf->save($file_path);    
        return asset('sales/sales_report.pdf');
    }
}

