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

        $numRows = $sale->saleItems()->count();
        $maxHeight = ($numRows * 20) + 250;
        
        $pdf = Pdf::loadView('prints.sale-receipt', [
            'sale' => $sale,
            'shop' => Shop::first(),
        ]);
        $pdf->setPaper([0, 0, $customWidth, $maxHeight], 'portrait');
    
        if (!file_exists(storage_path('app/public/sales'))) {
            mkdir(storage_path('app/public/sales'), 0755, true);
        }

        $file_path = storage_path('app/public/sales/sale_receipt.pdf');
        $pdf->save($file_path);    
        return asset('storage/sales/sale_receipt.pdf');
    }

    public function generateSaleReceiptA4($sale_id): string {   
        $sale = Sale::findOrFail($sale_id); 
        
        $pdf = Pdf::loadView('prints.sale-receipt-a4', [
            'sale' => $sale,
            'shop' => Shop::first(),
        ]);
            
        if (!file_exists(storage_path('app/public/sales'))) {
            mkdir(storage_path('app/public/sales'), 0755, true);
        }

        $file_path = storage_path('app/public/sales/sale_receipt_a4.pdf');
        $pdf->save($file_path);    
        return asset('storage/sales/sale_receipt_a4.pdf');
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
    
        if (!file_exists(storage_path('app/public/sales'))) {
            mkdir(storage_path('app/public/sales'), 0755, true);
        }

        $file_path = storage_path('app/public/sales/sales_report.pdf');
        $pdf->save($file_path);
    
        return asset('storage/sales/sales_report.pdf');
    }
}

