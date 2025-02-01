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
    
        if (!is_dir(public_path('stocks'))) {
            mkdir(public_path('stocks'), 0755, true);
        }

        $file_path = public_path('stocks/stocks.pdf');
        $pdf->save($file_path);    
        return asset('stocks/stocks.pdf');
    }
}

