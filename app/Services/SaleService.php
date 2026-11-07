<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sale;
use App\Models\Shop;

final class SaleService
{
    public function generateSaleReceipt($sale_id, ?string $locale = null): string {  
        $sale = Sale::with(['saleItems.product', 'saleItems.productVariation', 'saleItems.warranty', 'customer', 'createdBy', 'payments'])->findOrFail($sale_id);
        $customWidth = 80 / 25.4 * 72; // 80 mm in PDF points

        $shop = Shop::first();
        $targetLocale = in_array($locale, ['en', 'fr'], true) ? $locale : app()->getLocale();
        $previousLocale = app()->getLocale();
        app()->setLocale($targetLocale);

        try {
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

            $receiptNo = $sale->receipt_no ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $sale->receipt_no) : "sale_{$sale->id}";
            $filename = "sale_receipt_{$receiptNo}_{$targetLocale}.pdf";
            $file_path = public_path("sales/{$filename}");
            $pdf->save($file_path);    
            $pdf->save(public_path('sales/sale_receipt.pdf'));

            return asset("sales/{$filename}") . '?v=' . time();
        } finally {
            app()->setLocale($previousLocale);
        }
    }    

    public function generateSaleReceiptA4($sale_id, ?string $locale = null): string {   
        $sale = Sale::with(['saleItems.product', 'saleItems.productVariation', 'saleItems.warranty', 'customer', 'createdBy', 'payments'])->findOrFail($sale_id); 
        
        $targetLocale = in_array($locale, ['en', 'fr'], true) ? $locale : app()->getLocale();
        $previousLocale = app()->getLocale();
        app()->setLocale($targetLocale);

        try {
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

            $receiptNo = $sale->receipt_no ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $sale->receipt_no) : "sale_{$sale->id}";
            $filename = "sale_receipt_a4_{$receiptNo}_{$targetLocale}.pdf";
            $file_path = public_path("sales/{$filename}");
            $pdf->save($file_path);    
            $pdf->save(public_path('sales/sale_receipt_a4.pdf'));

            return asset("sales/{$filename}") . '?v=' . time();
        } finally {
            app()->setLocale($previousLocale);
        }
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
