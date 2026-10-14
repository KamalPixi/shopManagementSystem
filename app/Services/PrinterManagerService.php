<?php

namespace App\Services;

use App\Models\Sale;
use App\Printing\WindowsSpoolerConnector;
use Symfony\Component\Process\Process;
use App\Models\Shop;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PrinterManagerService
{
    /**
     * Get the default printer configuration structure.
     */
    public function getDefaultSettings(): array
    {
        $defaultPosConnector = PHP_OS_FAMILY === 'Windows' ? 'windows' : 'cups';

        return [
            'pos' => [
                'printer_name' => '',
                'connection_type' => $defaultPosConnector, // 'windows', 'cups', 'network'
                'ip_address' => '',
                'port' => 9100,
                'paper_width' => '80mm', // '80mm' or '58mm'
                'open_drawer' => true,
                'cut_paper' => true,
                'auto_print_on_sale' => false,
            ],
            'a4' => [
                'printer_name' => '',
                'auto_print' => false,
            ],
            'barcode' => [
                'printer_name' => '',
            ],
        ];
    }

    /**
     * Get current configured printer settings from Shop.
     */
    public function getSettings(): array
    {
        $shop = Shop::first();
        $stored = $shop?->settings['printers'] ?? [];
        $defaults = $this->getDefaultSettings();

        return [
            'pos' => array_merge($defaults['pos'], $stored['pos'] ?? []),
            'a4' => array_merge($defaults['a4'], $stored['a4'] ?? []),
            'barcode' => array_merge($defaults['barcode'], $stored['barcode'] ?? []),
        ];
    }

    /**
     * Save printer settings into Shop settings.
     */
    public function saveSettings(array $newSettings): bool
    {
        $shop = Shop::first();
        if (!$shop) {
            return false;
        }

        $allSettings = $shop->settings ?? [];
        $currentPrinterSettings = $this->getSettings();

        $allSettings['printers'] = [
            'pos' => array_merge($currentPrinterSettings['pos'], $newSettings['pos'] ?? []),
            'a4' => array_merge($currentPrinterSettings['a4'], $newSettings['a4'] ?? []),
            'barcode' => array_merge($currentPrinterSettings['barcode'], $newSettings['barcode'] ?? []),
        ];

        $shop->settings = $allSettings;
        return $shop->save();
    }

    /**
     * Detect all printers currently installed on the host operating system.
     * Works on Windows and macOS/Linux.
     */
    public function detectSystemPrinters(): array
    {
        $printers = [];

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $printers = $this->detectWindowsPrinters();
            } else {
                $printers = $this->detectUnixPrinters();
            }
        } catch (\Throwable $e) {
            Log::warning('Error detecting system printers: ' . $e->getMessage());
        }

        // Decorate with helper suggestions (POS thermal vs standard A4)
        return array_map(function ($p) {
            $name = $p['name'] ?? '';
            $posKeywords = ['pos', 'thermal', 'receipt', 'xp-', 'xprinter', 'tm-t', 'rp80', 'rp58', 'tsp', 'star', 'epson tm', 'bixolon'];
            $a4Keywords = ['laserjet', 'deskjet', 'officejet', 'canon', 'brother', 'hp', 'pdf', 'xps', 'ricoh', 'epson l', 'xerox'];

            $isPos = false;
            foreach ($posKeywords as $k) {
                if (stripos($name, $k) !== false) {
                    $isPos = true;
                    break;
                }
            }

            $isA4 = false;
            foreach ($a4Keywords as $k) {
                if (stripos($name, $k) !== false) {
                    $isA4 = true;
                    break;
                }
            }

            return array_merge($p, [
                'is_pos_suggested' => $isPos,
                'is_a4_suggested' => $isA4,
            ]);
        }, $printers);
    }

    /**
     * Detect installed printers on Windows using PowerShell / WMIC.
     */
    protected function detectWindowsPrinters(): array
    {
        $printers = [];

        // Method 1: PowerShell Get-CimInstance (fast and reliable on Windows 10/11)
        $psCommand = 'powershell -NoProfile -Command "Get-CimInstance Win32_Printer | Select-Object Name, Default, PortName | ConvertTo-Json -Compress"';
        $output = @shell_exec($psCommand);

        if (!empty($output)) {
            $decoded = json_decode(trim($output), true);
            if (!empty($decoded)) {
                // If single printer, ConvertTo-Json returns an associative object instead of array
                $list = isset($decoded['Name']) ? [$decoded] : $decoded;
                foreach ($list as $item) {
                    if (!empty($item['Name'])) {
                        $printers[] = [
                            'name' => trim($item['Name']),
                            'is_default' => (bool) ($item['Default'] ?? false),
                            'port' => trim($item['PortName'] ?? ''),
                        ];
                    }
                }
            }
        }

        // Method 2: Fallback to wmic if PowerShell returned nothing
        if (empty($printers)) {
            $wmicOutput = [];
            @exec('wmic printer get Name,Default /format:csv 2>NUL', $wmicOutput);
            foreach ($wmicOutput as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, 'Node')) {
                    continue;
                }
                $parts = str_getcsv($line);
                if (count($parts) >= 3 && !empty($parts[2])) {
                    $printers[] = [
                        'name' => trim($parts[2]),
                        'is_default' => strcasecmp(trim($parts[1]), 'TRUE') === 0,
                        'port' => '',
                    ];
                }
            }
        }

        return $printers;
    }

    /**
     * Detect installed printers on macOS / Linux using CUPS.
     */
    protected function detectUnixPrinters(): array
    {
        $printers = [];
        $defaultPrinter = null;

        // Find default printer
        $defOutput = [];
        @exec('lpstat -d 2>/dev/null', $defOutput);
        if (!empty($defOutput[0]) && preg_match('/:\s*(\S+)/', $defOutput[0], $m)) {
            $defaultPrinter = trim($m[1]);
        }

        // Get printer names using lpstat -e (lists available destinations)
        $eOutput = [];
        @exec('lpstat -e 2>/dev/null', $eOutput);

        if (!empty($eOutput)) {
            foreach ($eOutput as $name) {
                $name = trim($name);
                if (!empty($name)) {
                    $printers[] = [
                        'name' => $name,
                        'is_default' => ($name === $defaultPrinter),
                        'port' => 'CUPS',
                    ];
                }
            }
        } else {
            // Fallback to lpstat -p
            $pOutput = [];
            @exec('lpstat -p 2>/dev/null', $pOutput);
            foreach ($pOutput as $line) {
                if (preg_match('/^printer\s+(\S+)/', trim($line), $m)) {
                    $printers[] = [
                        'name' => $m[1],
                        'is_default' => ($m[1] === $defaultPrinter),
                        'port' => 'CUPS',
                    ];
                }
            }
        }

        return $printers;
    }

    /**
     * Establish the ESC/POS printer connection for the configured POS printer.
     */
    public function getPosPrinterConnector(): array
    {
        $settings = $this->getSettings()['pos'];
        $connType = $settings['connection_type'] ?? (PHP_OS_FAMILY === 'Windows' ? 'windows' : 'cups');
        $printerName = trim($settings['printer_name'] ?? '');

        // If no printer name specified, try to find default or first detected POS printer
        if (empty($printerName) && $connType !== 'network') {
            $detected = $this->detectSystemPrinters();
            $suggested = collect($detected)->firstWhere('is_pos_suggested', true);
            $default = collect($detected)->firstWhere('is_default', true);
            $printerName = $suggested['name'] ?? ($default['name'] ?? ($detected[0]['name'] ?? ''));
        }

        try {
            if ($connType === 'network') {
                $ip = trim($settings['ip_address'] ?? '');
                $port = (int) ($settings['port'] ?: 9100);
                if (empty($ip)) {
                    return [
                        'success' => false,
                        'message' => __('Network printer IP address is required.'),
                    ];
                }
                $connector = new NetworkPrintConnector($ip, $port, 3);
                $printer = new Printer($connector);
                return [
                    'success' => true,
                    'printer' => $printer,
                    'target' => "Network ($ip:$port)",
                    'settings' => $settings,
                ];
            }

            if (empty($printerName)) {
                return [
                    'success' => false,
                    'message' => __('No POS receipt printer configured or detected on this system.'),
                ];
            }

            if (PHP_OS_FAMILY === 'Windows') {
                $connector = preg_match('/^(LPT\d|COM\d)$|^smb:\/\//i', $printerName)
                    ? new WindowsPrintConnector($printerName)
                    : new WindowsSpoolerConnector($printerName);
                $printer = new Printer($connector);
                return [
                    'success' => true,
                    'printer' => $printer,
                    'target' => "Windows ($printerName)",
                    'settings' => $settings,
                ];
            } else {
                // macOS / Linux CUPS
                $connector = new CupsPrintConnector($printerName);
                $printer = new Printer($connector);
                return [
                    'success' => true,
                    'printer' => $printer,
                    'target' => "CUPS ($printerName)",
                    'settings' => $settings,
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => __('Cannot connect to printer [:target]: :err', [
                    'target' => $printerName ?: ($settings['ip_address'] ?? 'Unknown'),
                    'err' => $e->getMessage(),
                ]),
            ];
        }
    }

    /**
     * Print a sale receipt using the configured POS thermal printer.
     */
    public function printPosReceipt(Sale $sale): array
    {
        $connection = $this->getPosPrinterConnector();
        if (!$connection['success']) {
            return $connection;
        }

        /** @var Printer $printer */
        $printer = $connection['printer'];
        $settings = $connection['settings'];
        $shop = Shop::first();

        try {
            $is80mm = ($settings['paper_width'] ?? '80mm') === '80mm';
            $printWidth = $is80mm ? 48 : 32;
            $divider = str_repeat('-', $printWidth) . "\n";

            // Column widths for 48-char (80mm) paper
            $col1 = $is80mm ? 6 : 5;   // Qty
            $col2 = $is80mm ? 14 : 9;  // Unit Price
            $col3 = $is80mm ? 10 : 8;  // Discount
            $col4 = $is80mm ? 18 : 10; // Total

            // Open cash drawer kick if configured
            if (!empty($settings['open_drawer'])) {
                $printer->pulse();
            }

            // ── HEADER ─────────────────────────────────────────────────────
            // Use FONT_A only — FONT_B renders blurry on Xprinter / POS-80C drivers.
            // Shop name: centered, bold, double-size text — NO logo/image.
            $printer->setFont(Printer::FONT_A);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 2); // Double width + height = bold & razor-sharp
            $printer->text(($shop?->name ?? 'TECH PARISIEN') . "\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);

            if ($shop?->address) {
                $printer->text($shop->address . ', ' . ($shop->city ?? '') . "\n");
            }
            if ($shop?->contact_no) {
                $printer->text('Tel: ' . $shop->contact_no . "\n");
            }
            if ($shop?->email) {
                $printer->text($shop->email . "\n");
            }

            $printer->setEmphasis(true);
            $printer->text("\nSALES RECEIPT\n");
            $printer->setEmphasis(false);
            $printer->text($divider);

            // ── RECEIPT METADATA ──────────────────────────────────────────
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $currencyCode = $shop?->currency['code'] ?? 'EUR';
            $shopTimezone = $shop?->timezone ?? 'Europe/Paris';

            $printer->text('Receipt No.: ' . $sale->receipt_no . "\n");
            $printer->text('Date       : ' . $sale->created_at->timezone($shopTimezone)->format('d/m/Y H:i') . "\n");
            $printer->text('Cashier    : ' . ($sale->createdBy?->name ?? ($sale->createdBy?->username ?? '-')) . "\n");
            $printer->text('Payment    : ' . ucfirst(str_replace('_', ' ', $sale->payment_method ?: 'Cash')) . "\n");
            if ($sale->payment_reference) {
                $printer->text('Ref        : ' . $sale->payment_reference . "\n");
            }
            if ($sale->customer) {
                $printer->text('Customer   : ' . ($sale->customer->name ?: 'Walk In') . "\n");
            }
            $printer->text($divider);

            // ── COLUMN HEADER ─────────────────────────────────────────────
            $printer->setEmphasis(true);
            $printer->text(
                str_pad('Qty', $col1, ' ', STR_PAD_RIGHT) .
                str_pad('Unit Price', $col2, ' ', STR_PAD_LEFT) .
                str_pad('Disc', $col3, ' ', STR_PAD_LEFT) .
                str_pad('Total', $col4, ' ', STR_PAD_LEFT) . "\n"
            );
            $printer->setEmphasis(false);
            $printer->text($divider);

            // ── LINE ITEMS ────────────────────────────────────────────────
            foreach ($sale->saleItems as $item) {
                $name = $item->name ?? ($item->item_name ?? 'Item');
                if (!empty($item->productVariation?->variation_name)) {
                    $name .= ' (' . $item->productVariation->variation_name . ')';
                }
                $productName = mb_strimwidth($name, 0, $printWidth - 1, '...');
                $printer->text($productName . "\n");

                $sn = $item->productVariation?->serial_no ?: $item->product?->serial_no;
                if ($sn && $item->show_serial_on_receipt) {
                    $printer->text('  SN: ' . $sn . "\n");
                }

                $unit     = $item->quantity . ' x';
                $price    = number_format($item->price, 2);
                $discount = number_format(($item->discount ?? 0), 2);
                $amount   = number_format($item->total, 2);

                $printer->text(
                    str_pad($unit, $col1, ' ', STR_PAD_RIGHT) .
                    str_pad($price, $col2, ' ', STR_PAD_LEFT) .
                    str_pad($discount, $col3, ' ', STR_PAD_LEFT) .
                    str_pad($amount, $col4, ' ', STR_PAD_LEFT) . "\n"
                );

                $warrantyText = $item->warranty_description ?: ($item->warranty?->description ?: $item->warranty?->name);
                if ($warrantyText && $item->show_warranty_on_receipt) {
                    $wText = mb_strimwidth('  * Warranty: ' . $warrantyText, 0, $printWidth - 1, '...');
                    $printer->text($wText . "\n");
                }
            }

            $printer->text($divider);

            // ── TOTALS ────────────────────────────────────────────────────
            $subtotal    = number_format($sale->total_price - $sale->gst + $sale->discount, 2);
            $taxLabel    = ($shop?->tax_label ?? 'TVA') . ':';
            $gstAmount   = number_format($sale->gst, 2);
            $discountAmt = number_format($sale->discount, 2);
            $total       = number_format($sale->total_price, 2);

            $labelWidth = $is80mm ? 28 : 18;
            $valWidth   = $is80mm ? 20 : 14;

            $printer->text(str_pad('Sub Total:', $labelWidth) . str_pad($subtotal, $valWidth, ' ', STR_PAD_LEFT) . "\n");
            if ($sale->discount > 0) {
                $printer->text(str_pad('Discount:', $labelWidth) . str_pad('-' . $discountAmt, $valWidth, ' ', STR_PAD_LEFT) . "\n");
            }
            $printer->text(str_pad($taxLabel, $labelWidth) . str_pad($gstAmount, $valWidth, ' ', STR_PAD_LEFT) . "\n");
            $printer->text($divider);
            $printer->setEmphasis(true);
            $printer->text(str_pad("Total ($currencyCode):", $labelWidth) . str_pad($total, $valWidth, ' ', STR_PAD_LEFT) . "\n");
            $printer->setEmphasis(false);
            $printer->text($divider);

            // ── FOOTER ────────────────────────────────────────────────────
            // FONT_A throughout — avoids the blur caused by FONT_B on Xprinter drivers.
            $printer->setFont(Printer::FONT_A);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(true);
            $printer->text("\nThank you for your purchase!\n");
            $printer->setEmphasis(false);
            if ($shop?->contact_no) {
                $printer->text("For any inquiry, please call us\n");
                $printer->text($shop->contact_no . "\n");
            }
            $printer->text(now()->timezone($shopTimezone)->format('F j, Y g:i A') . "\n");
            $printer->feed(3);

            // Paper Cut
            if (!empty($settings['cut_paper'])) {
                $printer->cut();
            }

            $printer->close();

            return [
                'success' => true,
                'message' => __('Receipt sent to :target', ['target' => $connection['target']]),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => __('Printing failed: :err', ['err' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Send an A4 PDF document directly to the configured A4 office printer.
     */
    public function printA4Document(string $pdfFilePath, ?string $printerName = null, ?string $html = null): array
    {
        if (!file_exists($pdfFilePath)) {
            return [
                'success' => false,
                'message' => __('PDF file not found at: :path', ['path' => $pdfFilePath]),
            ];
        }

        $settings = $this->getSettings()['a4'];
        $targetPrinter = $printerName ?: trim($settings['printer_name'] ?? '');

        try {
            if ($html !== null && config('nativephp-internal.api_url')) {
                $response = app(\Native\Desktop\Client\Client::class)->post('system/print', [
                    'html' => rawurlencode($html),
                    'printer' => $targetPrinter,
                    'settings' => ['silent' => true, 'pageSize' => 'A4', 'printBackground' => true],
                ]);
                if (!$response->successful()) {
                    throw new \RuntimeException(__('The desktop print service rejected the job. Check the selected printer and its queue.'));
                }
            } else {
                if (PHP_OS_FAMILY === 'Windows') {
                    // The PDF fallback requires a PDF reader with a Print/PrintTo action.
                    $path = str_replace("'", "''", $pdfFilePath);
                    $target = str_replace("'", "''", $targetPrinter);
                    $script = "\$ErrorActionPreference = 'Stop'; try { ";
                    $script .= "Start-Process -FilePath '$path' -Verb ";
                    $script .= $targetPrinter !== '' ? "PrintTo -ArgumentList '\"$target\"'" : 'Print';
                    $script .= " -WindowStyle Hidden -ErrorAction Stop; exit 0 } catch { [Console]::Error.WriteLine(\$_.Exception.Message); exit 1 }";
                    $process = new Process(['powershell.exe', '-NoProfile', '-NonInteractive', '-EncodedCommand',
                        base64_encode(mb_convert_encoding($script, 'UTF-16LE', 'UTF-8'))]);
                } else {
                    $command = ['lpr'];
                    if ($targetPrinter !== '') {
                        array_push($command, '-P', $targetPrinter);
                    }
                    $command[] = $pdfFilePath;
                    $process = new Process($command);
                }
                $process->setTimeout(30);
                $process->run();
                if (!$process->isSuccessful()) {
                    throw new \RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()) ?: __('The print command failed.'));
                }
            }

            return [
                'success' => true,
                'message' => __('A4 print submitted to [:target]. Check the printer queue for output.', ['target' => $targetPrinter ?: __('Default Printer')]),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => __('A4 printing failed: :err', ['err' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Run a test print on the configured POS thermal receipt printer.
     */
    public function testPosPrint(): array
    {
        $connection = $this->getPosPrinterConnector();
        if (!$connection['success']) {
            return $connection;
        }

        /** @var Printer $printer */
        $printer = $connection['printer'];
        $settings = $connection['settings'];
        $shop = Shop::first();

        try {
            $is80mm = ($settings['paper_width'] ?? '80mm') === '80mm';
            $width = $is80mm ? 48 : 32;
            $divider = str_repeat('=', $width) . "\n";

            // Drawer kick if enabled
            if (!empty($settings['open_drawer'])) {
                $printer->pulse();
            }

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(1, 2);
            $printer->text("TEST RECEIPT\n");
            $printer->setTextSize(1, 1);
            $printer->text(($shop?->name ?? 'TECH PARISIEN') . "\n");
            $printer->setEmphasis(false);
            $printer->text($divider);

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Printer Target : " . $connection['target'] . "\n");
            $printer->text("Paper Width    : " . ($is80mm ? '80mm (48 chars)' : '58mm (32 chars)') . "\n");
            $printer->text("Cut Enabled    : " . (!empty($settings['cut_paper']) ? 'YES' : 'NO') . "\n");
            $printer->text("Drawer Kick    : " . (!empty($settings['open_drawer']) ? 'YES' : 'NO') . "\n");
            $printer->text("Test Timestamp : " . now()->format('Y-m-d H:i:s') . "\n");
            $printer->text($divider);

            // Text formatting check
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("[Left aligned text]\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("[Center aligned text]\n");
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("[Right aligned text]\n");

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text($divider);
            $printer->setEmphasis(true);
            $printer->text("Print test\nIf you can read this, receipt output is working.\n");
            $printer->setEmphasis(false);

            if (!empty($settings['cut_paper'])) {
                $printer->cut();
            }

            $printer->close();

            return [
                'success' => true,
                'message' => __('Test receipt sent to :target. Check the printer for output.', ['target' => $connection['target']]),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => __('Test print failed: :err', ['err' => $e->getMessage()]),
            ];
        }
    }

    /**
     * Run a test print on the configured A4 office printer.
     */
    public function testA4Print(): array
    {
        $settings = $this->getSettings()['a4'];
        $shop = Shop::first();

        // Create a temporary diagnostic test PDF
        $data = [
            'shop' => $shop,
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'printer_name' => $settings['printer_name'] ?: __('Default Office Printer'),
        ];

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: sans-serif; margin: 40px; color: #333; }
                .box { border: 2px solid #2563eb; padding: 25px; border-radius: 8px; }
                h1 { color: #1e40af; margin-top: 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
                th { background-color: #f3f4f6; }
                .success { color: #16a34a; font-size: 1.2rem; font-weight: bold; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="box">
                <h1>A4 Test Page - Shop Management System</h1>
                <p>This is a diagnostic print test to verify your A4 Invoice / Report printer connection.</p>
                <table>
                    <tr><th>Shop Name</th><td>{$shop?->name}</td></tr>
                    <tr><th>Target Printer</th><td>{$data['printer_name']}</td></tr>
                    <tr><th>Paper Size</th><td>A4 (210 x 297 mm)</td></tr>
                    <tr><th>Test Timestamp</th><td>{$data['timestamp']}</td></tr>
                </table>
                <p class="success">If this page printed, A4 output is working.</p>
            </div>
        </body>
        </html>
HTML;

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        if (!is_dir(public_path('sales'))) {
            mkdir(public_path('sales'), 0755, true);
        }

        $testPath = public_path('sales/a4_test_print.pdf');
        $pdf->save($testPath);

        // Send to printer
        $result = $this->printA4Document($testPath, $settings['printer_name'], $html);
        $result['pdf_url'] = asset('sales/a4_test_print.pdf');

        return $result;
    }
}
