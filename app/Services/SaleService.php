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

        $numRows = $sale->saleItems()->count(); // This should be dynamic based on your data
        $maxHeight = ($numRows * 20) + 230; // Approximate height per row
        
        $pdf = Pdf::loadView('prints.sale-receipt', [
            'sale' => $sale,
            'shop' => Shop::first(),
        ]);
        
        $pdf->setPaper([0, 0, $customWidth, $maxHeight], 'portrait');
    
        if (!file_exists(storage_path('app/public/sales'))) {
            mkdir(storage_path('app/public/sales'), 0755, true);
        }

        // Define the file path where the PDF will be saved
        $file_path = storage_path('app/public/sales/sale_receipt.pdf');
    
        // Save the generated PDF to the specified file path
        $pdf->save($file_path);
    
        // Return the publicly accessible URL for the PDF
        return asset('storage/sales/sale_receipt.pdf');
    }
}

