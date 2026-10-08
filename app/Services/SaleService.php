<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sale;
use App\Models\Shop;

final class SaleService
{
    public function generateSaleReceipt($sale_id): string {  
        $sale = Sale::with(['saleItems.product', 'saleItems.productVariation', 'saleItems.warranty', 'customer', 'createdBy'])->findOrFail($sale_id);
        $customWidth = 80 / 25.4 * 72; // 80 mm in PDF points

        $shop = Shop::first();
        $data = [
            'sale' => $sale,
            'shop' => $shop,
        ];

        // Measure the fully laid-out receipt, including wrapped details and footer.
        $receiptHeight = 0;
        $measurement = Pdf::loadView('prints.sale-receipt', $data)
            ->setPaper([0, 0, $customWidth, 14400 + strlen(view('prints.sale-receipt', $data)->render())], 'portrait')
            ->setCallbacks([[
                'event' => 'end_frame',
                'f' => function ($frame) use (&$receiptHeight) {
                    if ($frame->get_node()->nodeName === 'body') {
                        $box = $frame->get_border_box();
                        $receiptHeight = $box['y'] + $box['h'];
                    }
                },
            ]]);
        $measurement->render();

        $pdf = Pdf::loadView('prints.sale-receipt', $data)
            ->setPaper([0, 0, $customWidth, ceil($receiptHeight) + 2], 'portrait');

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
