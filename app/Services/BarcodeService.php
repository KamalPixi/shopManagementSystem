<?php

namespace App\Services;

use TCPDF;
use App\Models\Product;
use App\Models\Shop;

final class BarcodeService
{
    public function generateBarcode(array $data) {
        $pdf = new TCPDF('L', 'mm', array('35', '24'));

        // remove default header & footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 1, 0);
        $pdf->SetAutoPageBreak(TRUE, 0);

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $style = array(
            'position' => 'C',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'DejaVuSans',
            'fontsize' => 7,
            'stretchtext' => 4
        );

        $products = Product::whereIn('sku', $data['sku_array'])->get();
        $s = Shop::first();

        if (!is_dir(public_path('barcodes'))) {
            mkdir(public_path('barcodes'), 0755, true);
        }

        // create barcode
        foreach ($data['sku_array'] as $barcode) {
            $p = $products->where('sku', $barcode)->first();

            // genarate barcode
            $pdf->AddPage();
            // $pdf->write1DBarcode($barcode, 'C39', '', '5', '', 15, 0.4, $style, 'N');
            $pdf->SetFont('DejaVuSans', 'B', 6, '', false);
            $pdf->Cell(35, 0, $s->name, 0, 1, 'C');
            $pdf->Cell(35, 0, $p->name, 0, 1, 'C');
            $pdf->Cell(35, 0, $s->currency['code'] . ': '.$p->price, 0, 1, 'C');
            $pdf->SetFont('DejaVuSans', 'B', 8, '', false);
            $pdf->write1DBarcode($barcode, 'C128', '', '', '', 15, 0.4, $style, 'N');
            // $pdf->Cell(76, 0, 'RM: 100', 0, 1, 'C');

        }
        
        $file_path = public_path('barcodes/barcode.pdf');
        $pdf->Output($file_path, 'F');
        return asset('barcodes/barcode.pdf');
    }

    public function generateBarcode2(array $data) {
        $pdf = new TCPDF('L', 'mm', array('70', '24'));
        // remove default header & footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 1, 0);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => true,
            'font' => 'DejaVuSans',
            'fontsize' => 7,
            'stretchtext' => 4
        );
        $products = Product::whereIn('sku', $data['sku_array'])->get();
        $s = Shop::first();
        if (!is_dir(public_path('barcodes'))) {
            mkdir(public_path('barcodes'), 0755, true);
        }
        // chunk the SKUs into pairs for 2 per page
        $chunks = array_chunk($data['sku_array'], 2);
        $line_height = 3;
        $barcode_height = 11;
        $barcode_width = 20;
        // create barcodes
        foreach ($chunks as $chunk) {
            $pdf->AddPage();
            foreach ($chunk as $index => $barcode) {
                $p = $products->where('sku', $barcode)->first();
                
                // Left barcode: x=2, Right barcode: x=37 (with 1mm space in middle)
                $x = $index == 0 ? 2 : 37;
                $cx = $x + $barcode_width / 2;
                $y_barcode_start = 1 + 3 * $line_height + 1;
                $end_y = $y_barcode_start + $barcode_height;
                $cy = (1 + $end_y) / 2;
                $angle = -90;
                
                $pdf->StartTransform();
                $pdf->Rotate($angle, $cx, $cy);
                
                $y = 1;
                $pdf->SetFont('DejaVuSans', 'B', 6, '', false);
                $y += $line_height;
                $pdf->SetXY($x, $y);
                $pdf->Cell($barcode_width, $line_height, $p->name, 0, 0, 'C');
                $y += $line_height;
                $pdf->SetXY($x, $y);
                $pdf->Cell($barcode_width, $line_height, $s->currency['code'] . ': '.$p->price, 0, 0, 'C');
                $y += $line_height + 1; // space after texts
                $pdf->SetFont('DejaVuSans', 'B', 8, '', false);
                $pdf->write1DBarcode($barcode, 'C128', $x, $y, $barcode_width, $barcode_height, 0.4, $style, 'N');
                
                $pdf->StopTransform();
            }
        }
        
        $file_path = public_path('barcodes/barcode.pdf');
        $pdf->Output($file_path, 'F');
        
        $this->printPdf($pdf);
        return asset('barcodes/barcode.pdf');
    }


    public function printPdf($pdf, $printerName = 'TSC_TE244')
    {
        $tempFile = '/tmp/barcode_' . uniqid() . '.pdf';
        $pdf->Output($tempFile, 'F'); // Use Output with 'F' to save to file, not save()
        
        // Print PDF
        $printerName = escapeshellarg($printerName);
        exec("lpr -P $printerName $tempFile");
        
        // Clean up
        sleep(1); // Wait for print job to be sent
        unlink($tempFile);
    }

    /*
    public function generateBarcode2(array $data) {
        $pdf = new TCPDF('L', 'mm', array('70', '24'));
        // remove default header & footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 1, 0);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => true,
            'font' => 'DejaVuSans',
            'fontsize' => 7,
            'stretchtext' => 4
        );
        $products = Product::whereIn('sku', $data['sku_array'])->get();
        $s = Shop::first();
        if (!is_dir(public_path('barcodes'))) {
            mkdir(public_path('barcodes'), 0755, true);
        }
        // chunk the SKUs into pairs for 2 per page
        $chunks = array_chunk($data['sku_array'], 2);
        // create barcodes
        foreach ($chunks as $chunk) {
            $pdf->AddPage();
            foreach ($chunk as $index => $barcode) {
                $p = $products->where('sku', $barcode)->first();
                
                // Left barcode: x=2, Right barcode: x=37 (with 1mm space in middle)
                $x = $index == 0 ? 2 : 37;
                $barcode_width = 31;
                
                $line_height = 3;
                $y = 1;
                $pdf->SetFont('DejaVuSans', 'B', 6, '', false);
                $pdf->SetXY($x, $y);
                $pdf->Cell($barcode_width, $line_height, $s->name, 0, 0, 'C');
                $y += $line_height;
                $pdf->SetXY($x, $y);
                $pdf->Cell($barcode_width, $line_height, $p->name, 0, 0, 'C');
                $y += $line_height;
                $pdf->SetXY($x, $y);
                $pdf->Cell($barcode_width, $line_height, $s->currency['code'] . ': '.$p->price, 0, 0, 'C');
                $y += $line_height + 1; // space after texts
                $pdf->SetFont('DejaVuSans', 'B', 8, '', false);
                $pdf->write1DBarcode($barcode, 'C128', $x, $y, $barcode_width, 11, 0.4, $style, 'N');
            }
        }
        
        $file_path = public_path('barcodes/barcode.pdf');
        $pdf->Output($file_path, 'F');
        return asset('barcodes/barcode.pdf');
    }
    */
}
