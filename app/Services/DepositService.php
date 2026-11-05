<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Deposit;
use App\Models\Shop;

final class DepositService
{
    public function generateDepositReceipt($deposit_id, ?string $locale = null): string
    {
        $deposit = Deposit::with(['customer', 'createdBy'])->findOrFail($deposit_id);
        $customWidth = 80 / 25.4 * 72; // 80 mm in PDF points

        $shop = Shop::first();
        $targetLocale = in_array($locale, ['en', 'fr'], true) ? $locale : app()->getLocale();
        $previousLocale = app()->getLocale();
        app()->setLocale($targetLocale);

        try {
            $data = [
                'deposit' => $deposit,
                'shop' => $shop,
            ];

            $receiptHeight = 0;
            $measurement = Pdf::loadView('prints.deposit-receipt', $data)
                ->setPaper([0, 0, $customWidth, 14400 + strlen(view('prints.deposit-receipt', $data)->render())], 'portrait')
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

            $pdf = Pdf::loadView('prints.deposit-receipt', $data)
                ->setPaper([0, 0, $customWidth, ceil($receiptHeight) + 2], 'portrait');

            if (!is_dir(public_path('deposit-receipts'))) {
                mkdir(public_path('deposit-receipts'), 0755, true);
            }

            $ref = preg_replace('/[^a-zA-Z0-9_-]/', '_', $deposit->reference_no ?: "dep_{$deposit->id}");
            $filename = "deposit_receipt_{$ref}_{$targetLocale}.pdf";
            $file_path = public_path("deposit-receipts/{$filename}");
            $pdf->save($file_path);

            return asset("deposit-receipts/{$filename}") . '?v=' . time();
        } finally {
            app()->setLocale($previousLocale);
        }
    }

    public function generateDepositReceiptA4($deposit_id, ?string $locale = null): string
    {
        $deposit = Deposit::with(['customer', 'createdBy'])->findOrFail($deposit_id);

        $targetLocale = in_array($locale, ['en', 'fr'], true) ? $locale : app()->getLocale();
        $previousLocale = app()->getLocale();
        app()->setLocale($targetLocale);

        try {
            $pdf = Pdf::loadView('prints.deposit-receipt-a4', [
                'deposit' => $deposit,
                'shop' => Shop::first(),
            ])->setPaper('a4', 'portrait')->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

            if (!is_dir(public_path('deposit-receipts'))) {
                mkdir(public_path('deposit-receipts'), 0755, true);
            }

            $ref = preg_replace('/[^a-zA-Z0-9_-]/', '_', $deposit->reference_no ?: "dep_{$deposit->id}");
            $filename = "deposit_receipt_a4_{$ref}_{$targetLocale}.pdf";
            $file_path = public_path("deposit-receipts/{$filename}");
            $pdf->save($file_path);

            return asset("deposit-receipts/{$filename}") . '?v=' . time();
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
