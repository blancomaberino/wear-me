<?php

namespace Tests\Unit\Services;

use App\Services\ColorNameMapper;
use App\Services\GarmentColorExtractor;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GarmentColorExtractorTest extends TestCase
{
    private GarmentColorExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->extractor = new GarmentColorExtractor(new ColorNameMapper());
    }

    public function test_returns_empty_array_for_nonexistent_file(): void
    {
        $result = $this->extractor->extract('nonexistent.jpg');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_returns_empty_array_for_invalid_image(): void
    {
        // Create a text file with .jpg extension
        $path = 'invalid.jpg';
        Storage::disk('public')->put($path, 'This is not an image file, just plain text.');

        $result = $this->extractor->extract($path);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_extracts_colors_from_solid_red_image(): void
    {
        $filename = $this->createTestImage(200, 200, [255, 0, 0], 'red.jpg');

        $result = $this->extractor->extract($filename);

        $this->assertNotEmpty($result);

        // Check that at least one color is in the red family
        $colorNames = array_column($result, 'name');
        $hasRedFamily = false;
        foreach ($colorNames as $name) {
            if (str_contains($name, 'Red') || str_contains($name, 'Scarlet') || str_contains($name, 'Crimson')) {
                $hasRedFamily = true;
                break;
            }
        }
        $this->assertTrue($hasRedFamily, 'Expected at least one red family color');
    }

    public function test_extracts_colors_from_solid_blue_image(): void
    {
        $filename = $this->createTestImage(200, 200, [0, 0, 255], 'blue.jpg');

        $result = $this->extractor->extract($filename);

        $this->assertNotEmpty($result);

        // Check that at least one color is in the blue family
        $colorNames = array_column($result, 'name');
        $hasBlueFamily = false;
        foreach ($colorNames as $name) {
            if (str_contains($name, 'Blue') || str_contains($name, 'Navy') || str_contains($name, 'Royal')) {
                $hasBlueFamily = true;
                break;
            }
        }
        $this->assertTrue($hasBlueFamily, 'Expected at least one blue family color');
    }

    public function test_extracts_colors_from_multi_color_image(): void
    {
        $filename = $this->createMultiColorImage(200, 200, 'multi.jpg');

        $result = $this->extractor->extract($filename);

        $this->assertNotEmpty($result);
        $this->assertGreaterThanOrEqual(2, count($result), 'Expected at least 2 colors from multi-color image');
    }

    public function test_returns_max_five_colors(): void
    {
        $filename = $this->createManyColorsImage(300, 300, 'many-colors.jpg');
        $result = $this->extractor->extract($filename);
        $this->assertNotEmpty($result);
        $this->assertLessThanOrEqual(5, count($result));
    }

    public function test_filters_white_background(): void
    {
        $filename = $this->createImageWithWhiteBackground(200, 200, 'white-bg.jpg');

        $result = $this->extractor->extract($filename);

        $this->assertNotEmpty($result, 'Should detect colors from image with white background');
        $names = array_column($result, 'name');
        $this->assertNotContains('White', $names, 'White background should be filtered out');
        $this->assertNotContains('Off-White', $names, 'Off-White background should be filtered out');
    }

    public function test_result_structure_has_hex_and_name(): void
    {
        $filename = $this->createTestImage(200, 200, [255, 0, 0], 'struct-test.jpg');

        $result = $this->extractor->extract($filename);

        $this->assertNotEmpty($result);

        foreach ($result as $color) {
            $this->assertArrayHasKey('hex', $color);
            $this->assertArrayHasKey('name', $color);
        }
    }

    public function test_hex_values_are_valid_format(): void
    {
        $filename = $this->createTestImage(200, 200, [0, 128, 255], 'hex-test.jpg');

        $result = $this->extractor->extract($filename);

        $this->assertNotEmpty($result);

        foreach ($result as $color) {
            $this->assertMatchesRegularExpression(
                '/^#[0-9A-F]{6}$/',
                $color['hex'],
                'Hex value should match format #RRGGBB'
            );
        }
    }

    public function test_handles_png_format(): void
    {
        $filename = $this->createTestImage(200, 200, [0, 255, 0], 'green.png', 'png');

        $result = $this->extractor->extract($filename);

        $this->assertNotEmpty($result);

        // Verify it extracted colors from the PNG
        $this->assertIsArray($result);
        foreach ($result as $color) {
            $this->assertArrayHasKey('hex', $color);
            $this->assertArrayHasKey('name', $color);
        }
    }

    public function test_handles_very_small_image(): void
    {
        $filename = $this->createTestImage(5, 5, [128, 0, 128], 'tiny.jpg');

        $result = $this->extractor->extract($filename);

        // Very small images might return empty array or valid colors
        // Both are acceptable behavior given the filtering logic
        $this->assertIsArray($result);

        // If it does return colors, they should be valid
        if (!empty($result)) {
            foreach ($result as $color) {
                $this->assertArrayHasKey('hex', $color);
                $this->assertArrayHasKey('name', $color);
            }
        }
    }

    /**
     * Create a test image with solid color
     */
    private function createTestImage(int $width, int $height, array $color, string $filename, string $format = 'jpeg'): string
    {
        $image = imagecreatetruecolor($width, $height);
        $fill = imagecolorallocate($image, $color[0], $color[1], $color[2]);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $fill);

        $path = Storage::disk('public')->path($filename);

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        match ($format) {
            'png' => imagepng($image, $path),
            default => imagejpeg($image, $path, 90),
        };
        imagedestroy($image);

        return $filename;
    }

    /**
     * Create a multi-color image (top half red, bottom half blue)
     */
    private function createMultiColorImage(int $width, int $height, string $filename): string
    {
        $image = imagecreatetruecolor($width, $height);

        // Top half red
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefilledrectangle($image, 0, 0, $width - 1, intdiv($height, 2) - 1, $red);

        // Bottom half blue
        $blue = imagecolorallocate($image, 0, 0, 255);
        imagefilledrectangle($image, 0, intdiv($height, 2), $width - 1, $height - 1, $blue);

        $path = Storage::disk('public')->path($filename);

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return $filename;
    }

    /**
     * Create an image with mostly white background and a large red square (~30% area)
     */
    private function createImageWithWhiteBackground(int $width, int $height, string $filename): string
    {
        $image = imagecreatetruecolor($width, $height);

        // Fill with white
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $white);

        // Draw a red square covering ~30% of the image area
        $squareSize = (int) round(sqrt($width * $height * 0.3));
        $red = imagecolorallocate($image, 255, 0, 0);
        $x1 = intdiv($width - $squareSize, 2);
        $y1 = intdiv($height - $squareSize, 2);
        imagefilledrectangle($image, $x1, $y1, $x1 + $squareSize - 1, $y1 + $squareSize - 1, $red);

        $path = Storage::disk('public')->path($filename);

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return $filename;
    }

    /**
     * Create an image with many color bands (7 colors) for testing max colors
     */
    private function createManyColorsImage(int $width, int $height, string $filename): string
    {
        $image = imagecreatetruecolor($width, $height);
        $colors = [
            [255, 0, 0],     // Red
            [0, 0, 255],     // Blue
            [0, 128, 0],     // Green
            [255, 255, 0],   // Yellow
            [128, 0, 128],   // Purple
            [255, 165, 0],   // Orange
            [0, 128, 128],   // Teal
        ];
        $bandHeight = intdiv($height, count($colors));
        foreach ($colors as $i => $c) {
            $fill = imagecolorallocate($image, $c[0], $c[1], $c[2]);
            $y1 = $i * $bandHeight;
            $y2 = ($i === count($colors) - 1) ? $height - 1 : ($y1 + $bandHeight - 1);
            imagefilledrectangle($image, 0, $y1, $width - 1, $y2, $fill);
        }

        $path = Storage::disk('public')->path($filename);

        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return $filename;
    }
}
