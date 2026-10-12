<?php

namespace App\Services;

use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use App\Models\Shop;
use App\Models\Sale;

final class PrintService
{
    private function detectPosPrinterNameWin(): ?string
    {
        $output = [];
        $returnCode = 0;
        // List printers on Windows (wmic is built-in)
        exec('wmic printer list brief', $output, $returnCode);

        if ($returnCode !== 0 || empty($output)) {
            return null;
        }

        $posKeywords = ['Xprinter'];
        $printerName = null;

        foreach ($output as $line) {
            $line = trim($line);
            if (strpos($line, 'Name=') === 0) {
                // Extract name: "Name=EPSON TM-T20"
                preg_match('/Name=\s*(.+)/', $line, $matches);
                if (!empty($matches[1])) {
                    $name = trim($matches[1]);
                    // Check if it's a POS printer
                    foreach ($posKeywords as $keyword) {
                        if (stripos($name, $keyword) !== false) {
                            $printerName = $name;
                            break 2;
                        }
                    }
                }
            }
        }

        return $printerName;
    }

    private function detectPosPrinterNameMac(): ?string
    {
        $output = [];
        $returnCode = 0;
        exec('lpstat -p', $output, $returnCode);

        if ($returnCode !== 0 || empty($output)) {
            return null;
        }

        $posKeywords = ['Xprinter'];

        foreach ($output as $line) {
            if (strpos($line, 'printer ') === 0) {
                preg_match('/printer\s+(\S+)/', $line, $matches);
                if (!empty($matches[1])) {
                    $name = $matches[1];
                    // Check if it's a POS printer
                    foreach ($posKeywords as $keyword) {
                        if (stripos($name, $keyword) !== false) {
                            return $name;
                        }
                    }
                }
            }
        }

        return null;
    }


    public function printReceipt(Sale $sale): string
    {
        try {
            $manager = app(PrinterManagerService::class);
            $result = $manager->printPosReceipt($sale);
            return $result['message'];
        } catch (\Throwable $e) {
            return 'Printer error: ' . $e->getMessage();
        }
    }

    public function printReceiptMac(Sale $sale)
    {
        $printerName = $this->detectPosPrinterNameMac();
        if (!$printerName) {
            return 'No CUPS printer detected on Mac. Add your printer as a raw queue first.';
        }
        
        $shop = Shop::firstOrFail();

        try {
            $connector = new CupsPrintConnector($printerName);
            $printer = new Printer($connector);
        } catch (\Throwable $e) {
            return 'Cannot connect to Mac printer: ' . $e->getMessage();
        }

        try {
            $printWidth = 48;
            $divider = str_repeat("-", $printWidth) . "\n";
            $col1 = 8;  // Unit
            $col2 = 12; // Price RM
            $col3 = 12; // Disc
            $col4 = 16; // Amount

            // Header
            $printer->setEmphasis(true);
            $printer->text("SALE RECEIPT\n");
            $printer->text("\n");
            $printer->setEmphasis(false);

            $printer->text(($shop->name ?? 'TECH PARISIEN') . "\n");
            $printer->text("202501028219 (1629631-W)\n");
            $printer->text($shop->address . ", " . $shop->city . "\n");
            $printer->text($shop->postal_code . " " . $shop->state . "\n");
            $printer->text("Contact No.: " . $shop->contact_no . "\n");
            $printer->text($divider);

            $currencyCode = $shop->currency['code'] ?? 'EUR';
            $shopTimezone = $shop->timezone ?? 'Europe/Paris';

            $printer->text("Receipt No.: " . $sale?->receipt_no . "\n");
            $printer->text("Date       : " . $sale?->created_at->timezone($shopTimezone)->format('d/m/Y h:i:s A') . "\n");
            $printer->text("Cashier    : " . ($sale?->createdBy?->name ?? ($sale?->createdBy?->username ?? '-')) . "\n");
            $printer->text("Payment    : " . ucfirst(str_replace('_', ' ', $sale?->payment_method ?: 'Cash')) . "\n");
            if ($sale?->payment_reference) {
                $printer->text("Ref        : " . $sale->payment_reference . "\n");
            }
            $printer->text($divider);

            // Table Header
            $printer->setEmphasis(true);
            $printer->text("Item\n");
            $printer->setEmphasis(false);
            $printer->text(
                str_pad("Unit", $col1, ' ', STR_PAD_RIGHT) .
                str_pad("Price $currencyCode", $col2, ' ', STR_PAD_LEFT) .
                str_pad("Disc", $col3, ' ', STR_PAD_LEFT) .
                str_pad("Amount", $col4, ' ', STR_PAD_LEFT) . "\n"
            );
            $printer->text($divider);

            // Sale items
            foreach ($sale->saleItems as $item) {
                $productName = mb_strimwidth($item->name ?? 'Item', 0, $printWidth - 1, "...");
                $printer->text($productName . "\n");

                $sn = $item->productVariation?->serial_no ?: $item->product?->serial_no;
                if ($sn && $item->show_serial_on_receipt) {
                    $printer->text("  SN: " . $sn . "\n");
                }

                $unit = $item->quantity . ' Unit *';
                $price = number_format($item->price, 2);
                $discount = number_format(($item->discount ?? 0), 2);
                $amount = number_format($item->total, 2);

                $printer->text(
                    str_pad($unit, $col1, ' ', STR_PAD_RIGHT) .
                    str_pad($price, $col2, ' ', STR_PAD_LEFT) .
                    str_pad($discount, $col3, ' ', STR_PAD_LEFT) .
                    str_pad($amount, $col4, ' ', STR_PAD_LEFT) . "\n"
                );

                $warrantyText = $item->warranty_description ?: ($item->warranty?->description ?: $item->warranty?->name);
                if ($warrantyText && $item->show_warranty_on_receipt) {
                    $wText = mb_strimwidth("  * Warranty: " . $warrantyText, 0, $printWidth - 1, "...");
                    $printer->text($wText . "\n");
                }
            }

            $printer->text($divider);

            // Totals
            $subtotal = number_format($sale->total_price - $sale->gst + $sale->discount, 2);
            $gst = number_format($sale->gst, 2);
            $total = number_format($sale->total_price, 2);

            $taxLabel = ($shop->tax_label ?? 'GST') . ':';
            $printer->text(str_pad("Sub Total:", 25) . str_pad($subtotal, 20, ' ', STR_PAD_LEFT) . "\n");
            $printer->text(str_pad($taxLabel, 25) . str_pad($gst, 20, ' ', STR_PAD_LEFT) . "\n");
            $printer->setEmphasis(true);
            $printer->text(str_pad("Total:", 25) . str_pad($total, 20, ' ', STR_PAD_LEFT) . "\n");
            $printer->setEmphasis(false);

            $printer->text($divider);

            // Footer
            $printer->setEmphasis(false);
            $printer->setFont(Printer::FONT_B);  // Switch to smaller font
            $printer->setTextSize(1, 1);
            $printer->text("Goods sold are not returnable.\n");
            $printer->text("Please return your receipt and box\n");
            $printer->text("as proof of warranty service.\n\n");

            // Switch back
            $printer->setFont(Printer::FONT_A);

            $printer->setEmphasis(true);
            $printer->text("Thank you!\n");
            $printer->setEmphasis(false);

            $printer->cut();

            return "Printed successfully to: $printerName";
        } catch (\Throwable $e) {
            return $e->getMessage();
        } finally {
            try {
                if (isset($printer)) {
                    $printer->close();
                }
            } catch (\Throwable $e) {
                // Ignore close errors to prevent crashing
            }
        }
    }



    public function printReceiptWin(Sale $sale)
    {
        $printerName = "XP-80C";
        if (!$printerName) {
            return 'No printer configured.';
        }

        $shop = Shop::firstOrFail();

        try {
            $connector = new WindowsPrintConnector($printerName);
            $printer = new Printer($connector);
        } catch (\Throwable $e) {
            return 'Cannot connect to Windows printer: ' . $e->getMessage();
        }

        try {
            $printWidth = 48;
            $divider = str_repeat("-", $printWidth) . "\n";
            $col1 = 8;  // Unit
            $col2 = 12; // Price RM
            $col3 = 12; // Disc
            $col4 = 16; // Amount

            // Header
            $printer->setEmphasis(true);
            $printer->text("SALE RECEIPT\n");
            $printer->setEmphasis(false);
            
            $printer->text("\n");

            $printer->setEmphasis(true);
            $printer->text(($shop->name ?? 'TECH PARISIEN') . "\n");
            $printer->text("202501028219 (1629631-W)\n");
            $printer->text($shop->address . ", " . $shop->city . "\n");
            $printer->text($shop->postal_code . " " . $shop->state . "\n");
            $printer->text("Contact No.: " . $shop->contact_no . "\n");
            $printer->setEmphasis(false);

            $printer->text($divider);

            $currencyCode = $shop->currency['code'] ?? 'EUR';
            $shopTimezone = $shop->timezone ?? 'Europe/Paris';

            $printer->text("Receipt No.: " . $sale?->receipt_no . "\n");
            $printer->text("Date       : " . $sale?->created_at->timezone($shopTimezone)->format('d/m/Y h:i:s A') . "\n");
            $printer->text("Cashier    : " . ($sale?->createdBy?->name ?? ($sale?->createdBy?->username ?? '-')) . "\n");
            $printer->text("Payment    : " . ucfirst(str_replace('_', ' ', $sale?->payment_method ?: 'Cash')) . "\n");
            if ($sale?->payment_reference) {
                $printer->text("Ref        : " . $sale->payment_reference . "\n");
            }
            $printer->text($divider);

            // Table Header
            $printer->setEmphasis(true);
            $printer->text("Item\n");
            $printer->setEmphasis(false);
            $printer->text(
                str_pad("Unit", $col1, ' ', STR_PAD_RIGHT) .
                str_pad("Price $currencyCode", $col2, ' ', STR_PAD_LEFT) .
                str_pad("Disc", $col3, ' ', STR_PAD_LEFT) .
                str_pad("Amount", $col4, ' ', STR_PAD_LEFT) . "\n"
            );
            $printer->text($divider);

            // Sale items
            foreach ($sale->saleItems as $item) {
                $productName = mb_strimwidth($item->name ?? 'Item', 0, $printWidth - 1, "...");
                $printer->text($productName . "\n");

                $sn = $item->productVariation?->serial_no ?: $item->product?->serial_no;
                if ($sn && $item->show_serial_on_receipt) {
                    $printer->text("  SN: " . $sn . "\n");
                }

                $unit = $item->quantity . ' Unit *';
                $price = number_format($item->price, 2);
                $discount = number_format(($item->discount ?? 0), 2);
                $amount = number_format($item->total, 2);

                $printer->text(
                    str_pad($unit, $col1, ' ', STR_PAD_RIGHT) .
                    str_pad($price, $col2, ' ', STR_PAD_LEFT) .
                    str_pad($discount, $col3, ' ', STR_PAD_LEFT) .
                    str_pad($amount, $col4, ' ', STR_PAD_LEFT) . "\n"
                );

                $warrantyText = $item->warranty_description ?: ($item->warranty?->description ?: $item->warranty?->name);
                if ($warrantyText && $item->show_warranty_on_receipt) {
                    $wText = mb_strimwidth("  * Warranty: " . $warrantyText, 0, $printWidth - 1, "...");
                    $printer->text($wText . "\n");
                }
            }

            $printer->text($divider);

            // Totals
            $subtotal = number_format(($sale->total_price - $sale->gst), 2);
            $gst = number_format($sale->gst, 2);
            $total = number_format($sale->total_price, 2);

            $taxLabel = ($shop->tax_label ?? 'GST') . ':';
            $printer->text(str_pad("Sub Total:", 25) . str_pad($subtotal, 20, ' ', STR_PAD_LEFT) . "\n");
            $printer->text(str_pad($taxLabel, 25) . str_pad($gst, 20, ' ', STR_PAD_LEFT) . "\n");
            $printer->setEmphasis(true);
            $printer->text(str_pad("Total:", 25) . str_pad($total, 20, ' ', STR_PAD_LEFT) . "\n");
            $printer->setEmphasis(false);

            $printer->text($divider);

            // Footer
            $printer->setEmphasis(false);
            $printer->setFont(Printer::FONT_B);  // Switch to smaller font
            $printer->setTextSize(1, 1);
            $printer->text("Goods sold are not returnable.\n");
            $printer->text("Please return your receipt and box\n");
            $printer->text("as proof of warranty service.\n\n");

            // Switch back
            $printer->setFont(Printer::FONT_A);

            $printer->setEmphasis(true);
            $printer->text("Thank you!\n");
            $printer->setEmphasis(false);

            $printer->cut();

            return "Printed successfully to: $printerName";
        } catch (\Throwable $e) {
            return $e->getMessage();
        } finally {
            try {
                if (isset($printer)) {
                    $printer->close();
                }
            } catch (\Throwable $e) {
                // Ignore close errors to prevent crashing
            }
        }
    }
}
