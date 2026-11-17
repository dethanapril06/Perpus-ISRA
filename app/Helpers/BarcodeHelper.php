<?php

namespace App\Helpers;

use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeHelper
{
    /**
     * Generate Barcode SVG HTML string
     */
    public static function generateBarcodeSVG(string $code, int $width = 2, int $height = 50): string
    {
        try {
            $generator = new BarcodeGeneratorSVG();
            return $generator->getBarcode($code, $generator::TYPE_CODE_128, $width, $height);
        } catch (\Exception $e) {
            return '<div class="text-danger">Barcode Error: ' . e($e->getMessage()) . '</div>';
        }
    }

    /**
     * Generate Barcode Base64 PNG string
     */
    public static function generateBarcodeBase64(string $code, int $width = 2, int $height = 50): string
    {
        try {
            $generator = new BarcodeGeneratorPNG();
            $image = $generator->getBarcode($code, $generator::TYPE_CODE_128, $width, $height);
            return 'data:image/png;base64,' . base64_encode($image);
        } catch (\Exception $e) {
            return '';
        }
    }
}
