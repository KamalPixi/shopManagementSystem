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
}
