<?php

namespace App\Helpers;

class BarcodeHelper
{
    /**
     * Generate pure SVG Code 128 Barcode lines
     */
    public static function generateSvg(string $code, int $height = 40): string
    {
        $patterns = [
            '0' => '11011001100', '1' => '11001101100', '2' => '11001100110', '3' => '10010011000',
            '4' => '10010001100', '5' => '10001001100', '6' => '10011001000', '7' => '10011000100',
            '8' => '10001100100', '9' => '11001001000', 'A' => '11010010000', 'B' => '11010000100',
            'C' => '11000101000', 'D' => '11001010000', 'E' => '11001000010', 'F' => '11000010100',
            'G' => '10001101000', 'H' => '10001100010', 'I' => '11011101110', 'J' => '11000111010',
            '-' => '10110001110', ' ' => '10111100010',
        ];

        $clean = strtoupper(preg_replace('/[^0-9A-Z\-]/', '', $code));
        if (empty($clean)) $clean = '000000';

        $binary = '11010010000'; // Start code
        for ($i = 0; $i < strlen($clean); $i++) {
            $char = $clean[$i];
            $binary .= $patterns[$char] ?? '10010011000';
        }
        $binary .= '1100011101011'; // Stop code

        $barWidth = 2;
        $totalWidth = strlen($binary) * $barWidth;

        $svg = "<svg viewBox=\"0 0 {$totalWidth} {$height}\" width=\"100%\" height=\"{$height}\" xmlns=\"http://www.w3.org/2000/svg\">";
        for ($x = 0; $x < strlen($binary); $x++) {
            if ($binary[$x] === '1') {
                $xPos = $x * $barWidth;
                $svg .= "<rect x=\"{$xPos}\" y=\"0\" width=\"{$barWidth}\" height=\"{$height}\" fill=\"#000000\"/>";
            }
        }
        $svg .= '</svg>';

        return $svg;
    }
}