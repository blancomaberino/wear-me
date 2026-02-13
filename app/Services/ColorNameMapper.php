<?php

namespace App\Services;

class ColorNameMapper
{
    /**
     * Fashion color palette with hex values
     */
    private const COLOR_PALETTE = [
        // Neutrals
        'Black' => '#000000',
        'White' => '#FFFFFF',
        'Charcoal' => '#36454F',
        'Gray' => '#808080',
        'Light Gray' => '#D3D3D3',
        'Off-White' => '#FAF0E6',
        'Cream' => '#FFFDD0',
        'Ivory' => '#FFFFF0',

        // Blues
        'Navy' => '#000080',
        'Royal Blue' => '#4169E1',
        'Blue' => '#0000FF',
        'Sky Blue' => '#87CEEB',
        'Light Blue' => '#ADD8E6',
        'Baby Blue' => '#89CFF0',
        'Teal' => '#008080',
        'Turquoise' => '#40E0D0',
        'Cobalt' => '#0047AB',
        'Denim' => '#1560BD',
        'Powder Blue' => '#B0E0E6',
        'Steel Blue' => '#4682B4',

        // Reds
        'Red' => '#FF0000',
        'Crimson' => '#DC143C',
        'Burgundy' => '#800020',
        'Maroon' => '#800000',
        'Wine' => '#722F37',
        'Scarlet' => '#FF2400',
        'Cherry' => '#DE3163',
        'Coral' => '#FF7F50',
        'Tomato' => '#FF6347',
        'Brick Red' => '#CB4154',

        // Greens
        'Green' => '#008000',
        'Forest Green' => '#228B22',
        'Olive' => '#808000',
        'Sage' => '#BCB88A',
        'Emerald' => '#50C878',
        'Lime' => '#32CD32',
        'Mint' => '#98FF98',
        'Hunter Green' => '#355E3B',
        'Army Green' => '#4B5320',
        'Khaki' => '#C3B091',

        // Yellows/Oranges
        'Yellow' => '#FFD700',
        'Mustard' => '#FFDB58',
        'Gold' => '#DAA520',
        'Orange' => '#FF8C00',
        'Tangerine' => '#FF9966',
        'Peach' => '#FFCBA4',
        'Amber' => '#FFBF00',
        'Honey' => '#EB9605',
        'Rust' => '#B7410E',
        'Burnt Orange' => '#CC5500',

        // Purples/Pinks
        'Purple' => '#800080',
        'Lavender' => '#E6E6FA',
        'Plum' => '#8E4585',
        'Mauve' => '#E0B0FF',
        'Lilac' => '#C8A2C8',
        'Violet' => '#7F00FF',
        'Magenta' => '#FF00FF',
        'Pink' => '#FFC0CB',
        'Hot Pink' => '#FF69B4',
        'Rose' => '#FF007F',
        'Blush' => '#DE5D83',
        'Fuchsia' => '#FF77FF',
        'Dusty Rose' => '#DCAE96',

        // Browns/Tans
        'Brown' => '#8B4513',
        'Chocolate' => '#7B3F00',
        'Tan' => '#D2B48C',
        'Beige' => '#F5F5DC',
        'Camel' => '#C19A6B',
        'Taupe' => '#483C32',
        'Sand' => '#C2B280',
        'Mocha' => '#967969',
        'Espresso' => '#3C1414',
        'Cognac' => '#9A463D',
    ];

    /**
     * Cache for LAB values of palette colors
     */
    private array $labCache = [];

    /**
     * Get human-readable color name for a hex color
     */
    public function toName(string $hex): string
    {
        return $this->findNearestColor($hex)['name'];
    }

    /**
     * Get color name and hex values for a hex color
     */
    public function toNameAndHex(string $hex): array
    {
        $result = $this->findNearestColor($hex);

        return [
            'hex' => $this->normalizeHex($hex),
            'name' => $result['name'],
            'canonical_hex' => $result['hex'],
        ];
    }

    /**
     * Find the nearest color in the palette using LAB distance
     */
    private function findNearestColor(string $hex): array
    {
        $normalizedHex = $this->normalizeHex($hex);
        $rgb = $this->hexToRgb($normalizedHex);
        $inputLab = $this->rgbToLab($rgb[0], $rgb[1], $rgb[2]);

        $minDistance = PHP_FLOAT_MAX;
        $nearestName = 'Gray';
        $nearestHex = '#808080';

        foreach (self::COLOR_PALETTE as $name => $paletteHex) {
            $paletteLab = $this->getPaletteLab($paletteHex);
            $distance = $this->labDistance($inputLab, $paletteLab);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestName = $name;
                $nearestHex = $paletteHex;
            }
        }

        return [
            'name' => $nearestName,
            'hex' => $nearestHex,
        ];
    }

    /**
     * Get cached LAB values for a palette color
     */
    private function getPaletteLab(string $hex): array
    {
        if (!isset($this->labCache[$hex])) {
            $rgb = $this->hexToRgb($hex);
            $this->labCache[$hex] = $this->rgbToLab($rgb[0], $rgb[1], $rgb[2]);
        }

        return $this->labCache[$hex];
    }

    /**
     * Normalize hex color (ensure # prefix and uppercase)
     */
    private function normalizeHex(string $hex): string
    {
        $hex = ltrim($hex, '#');
        return '#' . strtoupper($hex);
    }

    /**
     * Convert hex color to RGB
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Convert RGB to LAB color space
     *
     * Uses sRGB → XYZ → LAB transformation with D65 illuminant
     */
    private function rgbToLab(int $r, int $g, int $b): array
    {
        // Convert RGB (0-255) to linear RGB (0-1)
        $linearR = $this->srgbToLinear($r / 255.0);
        $linearG = $this->srgbToLinear($g / 255.0);
        $linearB = $this->srgbToLinear($b / 255.0);

        // Convert linear RGB to XYZ using sRGB D65 matrix
        $x = $linearR * 0.4124564 + $linearG * 0.3575761 + $linearB * 0.1804375;
        $y = $linearR * 0.2126729 + $linearG * 0.7151522 + $linearB * 0.0721750;
        $z = $linearR * 0.0193339 + $linearG * 0.1191920 + $linearB * 0.9503041;

        // Normalize by D65 illuminant
        $x = $x / 0.95047;
        $y = $y / 1.00000;
        $z = $z / 1.08883;

        // Convert XYZ to LAB
        $fx = $this->xyzToLabHelper($x);
        $fy = $this->xyzToLabHelper($y);
        $fz = $this->xyzToLabHelper($z);

        $L = 116.0 * $fy - 16.0;
        $a = 500.0 * ($fx - $fy);
        $bLab = 200.0 * ($fy - $fz);

        return [$L, $a, $bLab];
    }

    /**
     * Convert sRGB to linear RGB
     */
    private function srgbToLinear(float $c): float
    {
        if ($c <= 0.04045) {
            return $c / 12.92;
        }

        return pow(($c + 0.055) / 1.055, 2.4);
    }

    /**
     * Helper function for XYZ to LAB conversion
     */
    private function xyzToLabHelper(float $t): float
    {
        $delta = 6.0 / 29.0;

        if ($t > pow($delta, 3)) {
            return pow($t, 1.0 / 3.0);
        }

        return $t / (3.0 * pow($delta, 2)) + 4.0 / 29.0;
    }

    /**
     * Calculate CIE76 distance between two LAB colors
     */
    private function labDistance(array $lab1, array $lab2): float
    {
        $dL = $lab1[0] - $lab2[0];
        $da = $lab1[1] - $lab2[1];
        $db = $lab1[2] - $lab2[2];

        return sqrt($dL * $dL + $da * $da + $db * $db);
    }
}
