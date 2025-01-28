<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

final class StockService
{
    public function printStocks($data): string {
        $options = [
            'default_paper_orientation' => 'landscape',
            'default_paper_size' => 'a4',
            'margin_left' => 0, // Minimal left margin in millimeters
            'margin_right' => 0 // Minimal right margin in millimeters
        ];
    
        // Apply the options while generating the PDF
        $pdf = Pdf::loadView('prints.stocks', $data)
            ->setPaper('a4', 'landscape') // Ensure paper size and orientation
            ->setOptions($options);
    
        if (!file_exists(storage_path('app/public/stocks'))) {
            mkdir(storage_path('app/public/stocks'), 0755, true);
        }

        // Define the file path where the PDF will be saved
        $file_path = storage_path('app/public/stocks/stocks.pdf');
    
        // Save the generated PDF to the specified file path
        $pdf->save($file_path);
    
        // Return the publicly accessible URL for the PDF
        return asset('storage/stocks/stocks.pdf');
    }
}

