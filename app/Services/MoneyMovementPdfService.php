<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Shop;

final class MoneyMovementPdfService
{
    /**
     * Generate an A4 PDF report for Movement History Log with compact text layout.
     *
     * @param array $data
     * @return string URL of generated PDF
     */
    public function generateMovementHistoryPdf(array $data): string
    {
        $shop = $data['shop'] ?? Shop::first();
        $targetLocale = $data['locale'] ?? app()->getLocale();
        $previousLocale = app()->getLocale();
        app()->setLocale($targetLocale);

        try {
            $options = [
                'default_paper_orientation' => 'portrait',
                'default_paper_size' => 'a4',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ];

            $pdf = Pdf::loadView('prints.money-movements-report', array_merge($data, [
                'shop' => $shop,
            ]))->setPaper('a4', 'portrait')->setOptions($options);

            $directory = public_path('reports');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $timestamp = date('Ymd_His');
            $filename = "money_movements_report_{$timestamp}_{$targetLocale}.pdf";
            $filePath = "{$directory}/{$filename}";

            $pdf->save($filePath);
            // Also save to generic name for quick reference
            $pdf->save("{$directory}/money_movements_report.pdf");

            return asset("reports/{$filename}") . '?v=' . time();
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
