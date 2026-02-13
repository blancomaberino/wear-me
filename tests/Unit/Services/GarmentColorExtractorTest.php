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
        // Create a complex multi-color image
        $filename = $this->createMultiColorImage(200, 200, 'complex.jpg');

        $result = $this->extractor->extract($filename);

        $this->assertLessThanOrEqual(5, count($result), 'Should return at most 5 colors');
    }

    public function test_filters_white_background(): void
    {
        // Create image with 90% white and small red square
        $filename = $this->createImageWithWhiteBackground(200, 200, 'white-bg.jpg');

        $result = $this->extractor->extract($filename);

        // Should detect red, not white
        if (!empty($result)) {
            $colorNames = array_column($result, 'name');
            $this->assertNotContains('White', $colorNames, 'Should filter out white background');
            $this->assertNotContains('Off-White', $colorNames, 'Should filter out white background');
        }
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
        $filename = $this->createTestImagePng(200, 200, [0, 255, 0], 'green.png');

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
     * Create a test JPEG image with solid color
     */
    private function createTestImage(int $width, int $height, array $color, string $filename): string
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

        imagejpeg($image, $path, 90);
        imagedestroy($image);

        return $filename;
    }

    /**
     * Create a test PNG image with solid color
     */
    private function createTestImagePng(int $width, int $height, array $color, string $filename): string
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

        imagepng($image, $path);
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
     * Create an image with mostly white background and a small red square
     */
    private function createImageWithWhiteBackground(int $width, int $height, string $filename): string
    {
        $image = imagecreatetruecolor($width, $height);

        // Fill with white
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $white);

        // Add small red square (10% of image)
        $red = imagecolorallocate($image, 255, 0, 0);
        $squareSize = intdiv($width, 10);
        imagefilledrectangle(
            $image,
            intdiv($width, 2) - intdiv($squareSize, 2),
            intdiv($height, 2) - intdiv($squareSize, 2),
            intdiv($width, 2) + intdiv($squareSize, 2),
            intdiv($height, 2) + intdiv($squareSize, 2),
            $red
        );

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
