<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Models\Product;

class BarcodePdfService
{
    /**
     * Generate PDF with 2 column barcodes (vertical orientation)
     * 
     * @param string $leftBarcode Left barcode data
     * @param string $leftPrice Left price
     * @param string $rightBarcode Right barcode data
     * @param string $rightPrice Right price
     * @return PDF
     */
    public function generatePdf($leftBarcode, $leftPrice, $rightBarcode, $rightPrice)
    {
        // Create barcode images
        $generator = new BarcodeGeneratorPNG();
        
        // Generate barcodes (rotated 90 degrees for vertical)
        $leftBarcodeImg = base64_encode($generator->getBarcode($leftBarcode, $generator::TYPE_CODE_128));
        $rightBarcodeImg = base64_encode($generator->getBarcode($rightBarcode, $generator::TYPE_CODE_128));
        
        // Create HTML for PDF
        $html = $this->generateHtml($leftBarcodeImg, $leftBarcode, $leftPrice, $rightBarcodeImg, $rightBarcode, $rightPrice);
        
        // Generate PDF using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        // 8cm width x 3cm height = 226.77pt x 85.04pt
        $pdf->setPaper([0, 0, 226.77, 85.04]);
        
        return $pdf;
    }
    
    /**
     * Generate PDF with multiple barcode pairs (multiple pages)
     * 
     * @param array $barcodes Array of barcode pairs [['left_code' => '', 'left_price' => '', 'right_code' => '', 'right_price' => ''], ...]
     * @return PDF
     */
    public function generateMultiplePdf($ids)
    {
        $products = Product::whereIn('id', $ids)->select('price', 'name', 'sku')->get();
        $barcodes = [];

        foreach ($products->chunk(2) as $pair) {
            $pair = $pair->values(); // ensures 0 and 1 indexes exist
            $left = $pair[0];
            $right = $pair[1] ?? null;

            $barcodes[] = [
                'left_title' => $left->name,
                'left_price' => $left->price,
                'left_code'  => $left->sku,
                'right_title' => $right->name ?? null,
                'right_price' => $right->price ?? null,
                'right_code'  => $right->sku ?? null,
            ];
        }


        $generator = new BarcodeGeneratorPNG();
        $pages = [];
        
        // Generate HTML for each page
        foreach ($barcodes as $pair) {
            $leftBarcodeImg = base64_encode($generator->getBarcode($pair['left_code'], $generator::TYPE_CODE_128));
            $rightBarcodeImg = base64_encode($generator->getBarcode($pair['right_code'], $generator::TYPE_CODE_128));
            
            $pages[] = $this->generateHtml(
                $leftBarcodeImg, 
                $pair['left_code'], 
                $pair['left_price'],
                $rightBarcodeImg, 
                $pair['right_code'], 
                $pair['right_price'],
                $pair['left_title'],
                $pair['right_title'],
            );
        }
        
        // Combine all pages into one HTML with page breaks
        $combinedHtml = $this->combinePages($pages);
        
        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($combinedHtml);
        $pdf->setPaper([0, 0, 226.77, 85.04]);
        
        return $pdf;
    }
    
    /**
     * Generate HTML for PDF - Landscape with rotated barcodes
     */
    private function generateHtml($leftImg, $leftCode, $leftPrice, $rightImg, $rightCode, $rightPrice, $leftTitle, $rightTitle)
    {

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                @page {
                    size: 80mm 34mm;
                    margin: 0;
                }
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                html, body {
                    width: 80mm;
                    height: 34mm;
                    margin: 0;
                    padding: 0;
                }
                table {
                    width: 80mm;
                    height: 34mm;
                    border-collapse: collapse;
                    table-layout: fixed;
                }
                td {
                    width: 40mm;
                    height: 34mm;
                    text-align: center;
                    vertical-align: middle;
                    position: relative;
                }
                .rotate-content {
                    transform: rotate(90deg);
                    transform-origin: center center;
                    display: inline-block;
                }
                .barcode {
                    max-width: 20mm;
                    height: 15;
                    display: block;
                    margin: 0 auto 0 auto;
                }
                .code {
                    font-size: 6pt;
                    font-family: Arial, sans-serif;
                    width: 20mm;
                    text-align: center;
                    margin: 0 auto 0 auto;
                    line-height: 1;
                    letter-spacing: 2;
                    /* border: 1px solid black; */
                    padding-left:2;
                    font-weight:bold;
                }
                .price {
                    font-size: 9pt;
                    font-family: Arial, sans-serif;
                    font-weight: bold;
                    color: #000;
                }
                .title {
                    font-size: 10pt;
                    font-family: Arial, sans-serif;
                    font-weight: bold;
                    color: #000;
                }
                .first {
                    padding-left: 3; 
                    padding-top: 6;
                }
            </style>
        </head>
        <body>
            <table>
                <tr>
                    <td>
                        <div class="rotate-content first">
                            <img src="data:image/png;base64,{$leftImg}" class="barcode" />
                            <div class="code">{$leftCode}</div>
                            <div class="title">{$leftTitle}</div>
                            <div class="price">RM {$leftPrice}</div>
                        </div>
                    </td>
                    <td>
                        <div class="rotate-content">
                            <img src="data:image/png;base64,{$rightImg}" class="barcode" />
                            <div class="code">{$rightCode}</div>
                            <div class="title">{$rightTitle}</div>
                            <div class="price">RM {$rightPrice}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        HTML;
    }
    
    /**
     * Combine multiple pages into one HTML document
     */
    private function combinePages($pages)
    {
        if (empty($pages)) {
            return '<!DOCTYPE html><html><head><style></style></head><body></body></html>';
        }

        $combinedBody = '';
        
        foreach ($pages as $index => $pageHtml) {
            // Extract body content from each page
            preg_match('/<body>(.*?)<\/body>/s', $pageHtml, $matches);
            $bodyContent = $matches[1] ?? '';
            
            // Add page break after each page except the last one
            if ($index < count($pages) - 1) {
                $bodyContent .= '<div style="page-break-after: always;"></div>';
            }
            
            $combinedBody .= $bodyContent;
        }
        
        // Use the style from the first page
        preg_match('/<style>(.*?)<\/style>/s', $pages[0], $styleMatches);
        $style = $styleMatches[1] ?? '';
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                {$style}
            </style>
        </head>
        <body>
            {$combinedBody}
        </body>
        </html>
        HTML;
    }
    
    /**
     * Print PDF directly to printer
     */
    public function printPdfMac($pdf, $printerName = 'TSC_TE244')
    {
        $tempFile = '/tmp/barcode_' . uniqid() . '.pdf';
        $pdf->save($tempFile);
        
        // Print PDF
        $printerName = escapeshellarg($printerName);
        exec("lpr -P $printerName $tempFile");
        
        // Clean up
        sleep(1); // Wait for print job to be sent
        if (is_file($tempFile)) {
            unlink($tempFile);
        }
    }

    /**
     * Print PDF directly to printer (Windows 10)
     */
    public function printPdfWin($pdf, $printerName = 'TSC TE244')
    {
        $tempFile = sys_get_temp_dir() . '\\barcode_' . uniqid() . '.pdf';
        $pdf->save($tempFile);
        
        // Print PDF using Windows print command
        $printerName = escapeshellarg($printerName);
        $printFile = escapeshellarg($tempFile);
        
        // Method 3: Using PowerShell (built-in Windows method)
        $command = "powershell -Command \"Start-Process -FilePath $printFile -Verb Print -WindowStyle Hidden\"";
        exec($command);
        
        // Clean up
        sleep(2); // Wait for print job to be sent
        if (is_file($tempFile)) {
            unlink($tempFile);
        }
    }
}
