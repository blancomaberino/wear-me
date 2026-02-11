<?php

namespace App\Services;

class ColorHarmonyService
{
    public function computeScore(array $hexColors): int
    {
        if (count($hexColors) < 2) return 100;

        $hsls = array_map([$this, 'hexToHsl'], $hexColors);

        // Filter out neutrals (low saturation)
        $chromatic = array_filter($hsls, fn ($c) => $c['s'] > 10 && $c['l'] > 10 && $c['l'] < 90);
        $chromatic = array_values($chromatic);

        if (count($chromatic) < 2) return 90;

        $hues = array_map(fn ($c) => $c['h'], $chromatic);
        $distances = [];
        for ($i = 0; $i < count($hues); $i++) {
            for ($j = $i + 1; $j < count($hues); $j++) {
                $distances[] = $this->angularDistance($hues[$i], $hues[$j]);
            }
        }

        $maxDist = max($distances);

        // Determine type and base score
        if ($maxDist < 15) {
            $score = 92; // Monochromatic
        } elseif ($maxDist < 45) {
            $score = 82; // Analogous
        } elseif (count(array_filter($distances, fn ($d) => $d >= 150 && $d <= 210)) > 0) {
            $score = 77; // Complementary
        } elseif (count(array_filter($distances, fn ($d) => $d >= 100 && $d <= 140)) > 0) {
            $score = 72; // Triadic
        } else {
            $score = 35; // Clashing
        }

        // Saturation harmony bonus
        $saturations = array_map(fn ($c) => $c['s'], $chromatic);
        $satRange = max($saturations) - min($saturations);
        if ($satRange < 20) $score += 5;
        elseif ($satRange > 50) $score -= 5;

        return min(100, max(0, $score));
    }

    private function hexToHsl(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return ['h' => 0, 's' => 0, 'l' => round($l * 100)];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        if ($max === $r) $h = (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6;
        elseif ($max === $g) $h = (($b - $r) / $d + 2) / 6;
        else $h = (($r - $g) / $d + 4) / 6;

        return [
            'h' => round($h * 360),
            's' => round($s * 100),
            'l' => round($l * 100),
        ];
    }

    private function angularDistance(float $h1, float $h2): float
    {
        $diff = abs($h1 - $h2);
        return min($diff, 360 - $diff);
    }
}
