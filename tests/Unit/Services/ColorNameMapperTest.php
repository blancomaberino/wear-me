<?php

namespace Tests\Unit\Services;

use App\Services\ColorNameMapper;
use Tests\TestCase;

class ColorNameMapperTest extends TestCase
{
    private ColorNameMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new ColorNameMapper();
    }

    public function test_exact_palette_colors_return_correct_names(): void
    {
        $this->assertEquals('Black', $this->mapper->toName('#000000'));
        $this->assertEquals('White', $this->mapper->toName('#FFFFFF'));
        $this->assertEquals('Navy', $this->mapper->toName('#000080'));
        $this->assertEquals('Red', $this->mapper->toName('#FF0000'));
        $this->assertEquals('Green', $this->mapper->toName('#008000'));
    }

    public function test_near_colors_map_to_closest_palette(): void
    {
        // Almost black
        $this->assertEquals('Black', $this->mapper->toName('#010101'));

        // Almost white
        $this->assertEquals('White', $this->mapper->toName('#FEFEFE'));

        // Almost navy
        $this->assertEquals('Navy', $this->mapper->toName('#000082'));

        // Almost red
        $this->assertEquals('Red', $this->mapper->toName('#FE0000'));

        // Almost green
        $this->assertEquals('Green', $this->mapper->toName('#008100'));
    }

    public function test_to_name_returns_string(): void
    {
        $result = $this->mapper->toName('#FF0000');

        $this->assertIsString($result);
        $this->assertEquals('Red', $result);
    }

    public function test_to_name_and_hex_returns_expected_structure(): void
    {
        $result = $this->mapper->toNameAndHex('#FF0000');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('hex', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('canonical_hex', $result);

        $this->assertEquals('#FF0000', $result['hex']);
        $this->assertEquals('Red', $result['name']);
        $this->assertEquals('#FF0000', $result['canonical_hex']);
    }

    public function test_hex_normalization_handles_lowercase(): void
    {
        $lowercase = $this->mapper->toName('#ff0000');
        $uppercase = $this->mapper->toName('#FF0000');

        $this->assertEquals($uppercase, $lowercase);
        $this->assertEquals('Red', $lowercase);
    }

    public function test_hex_normalization_handles_no_hash(): void
    {
        $withHash = $this->mapper->toName('#FF0000');
        $withoutHash = $this->mapper->toName('FF0000');

        $this->assertEquals($withHash, $withoutHash);
        $this->assertEquals('Red', $withoutHash);
    }

    public function test_hex_normalization_handles_three_char_shorthand(): void
    {
        $shorthand = $this->mapper->toName('#F00');
        $fullForm = $this->mapper->toName('#FF0000');

        $this->assertEquals($fullForm, $shorthand);
        $this->assertEquals('Red', $shorthand);

        // Also test with toNameAndHex to verify normalized output
        $result = $this->mapper->toNameAndHex('#F00');
        $this->assertEquals('#F00', $result['hex']); // Preserves input format in hex field
        $this->assertEquals('Red', $result['name']);
        $this->assertEquals('#FF0000', $result['canonical_hex']);
    }

    public function test_common_fashion_colors_detected(): void
    {
        // Denim blue
        $this->assertEquals('Denim', $this->mapper->toName('#1560BD'));

        // Burgundy
        $this->assertEquals('Burgundy', $this->mapper->toName('#800020'));

        // Khaki
        $this->assertEquals('Khaki', $this->mapper->toName('#C3B091'));

        // Lavender
        $this->assertEquals('Lavender', $this->mapper->toName('#E6E6FA'));

        // Coral
        $this->assertEquals('Coral', $this->mapper->toName('#FF7F50'));

        // Emerald
        $this->assertEquals('Emerald', $this->mapper->toName('#50C878'));

        // Mustard
        $this->assertEquals('Mustard', $this->mapper->toName('#FFDB58'));

        // Charcoal
        $this->assertEquals('Charcoal', $this->mapper->toName('#36454F'));
    }

    public function test_all_palette_colors_self_map(): void
    {
        $ref = new \ReflectionClass(ColorNameMapper::class);
        $palette = $ref->getConstant('COLOR_PALETTE');

        foreach ($palette as $expectedName => $hex) {
            $actualName = $this->mapper->toName($hex);
            $this->assertEquals(
                $expectedName,
                $actualName,
                "Color {$hex} should map to '{$expectedName}' but got '{$actualName}'"
            );
        }
    }

    public function test_mid_tone_gray_maps_to_gray(): void
    {
        // Exact gray
        $this->assertEquals('Gray', $this->mapper->toName('#808080'));

        // Near gray values
        $this->assertEquals('Gray', $this->mapper->toName('#7F7F7F'));
        $this->assertEquals('Gray', $this->mapper->toName('#818181'));
    }

    public function test_warm_vs_cool_distinction(): void
    {
        // Warm brown tones
        $this->assertEquals('Tan', $this->mapper->toName('#D2B48C'));
        $this->assertEquals('Camel', $this->mapper->toName('#C19A6B'));
        $this->assertEquals('Brown', $this->mapper->toName('#8B4513'));

        // Cool gray tones
        $this->assertEquals('Gray', $this->mapper->toName('#808080'));
        $this->assertEquals('Charcoal', $this->mapper->toName('#36454F'));
        $this->assertEquals('Light Gray', $this->mapper->toName('#D3D3D3'));

        // Ensure warm brown doesn't map to cool gray
        $warmBrown = $this->mapper->toName('#A67B5B'); // Mid brown
        $this->assertNotEquals('Gray', $warmBrown);
        $this->assertNotEquals('Charcoal', $warmBrown);
    }

    public function test_hex_shorthand_normalization_in_to_name_and_hex(): void
    {
        // 3-char shorthand should preserve original format in 'hex' field
        $result = $this->mapper->toNameAndHex('#0F0');
        $this->assertEquals('#0F0', $result['hex']);
        // #0F0 = #00FF00 which is bright green, could map to Lime or Green
        $this->assertContains($result['name'], ['Lime', 'Green']);

        // But canonical should be full palette hex
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/', $result['canonical_hex']);
    }

    public function test_case_insensitivity_in_to_name_and_hex(): void
    {
        $lower = $this->mapper->toNameAndHex('#ff0000');
        $upper = $this->mapper->toNameAndHex('#FF0000');
        $mixed = $this->mapper->toNameAndHex('#Ff0000');

        // All should return same name and canonical hex
        $this->assertEquals($upper['name'], $lower['name']);
        $this->assertEquals($upper['canonical_hex'], $lower['canonical_hex']);
        $this->assertEquals($upper['name'], $mixed['name']);

        // But hex field should preserve original input case (normalized with #)
        $this->assertEquals('#FF0000', $upper['hex']);
        $this->assertEquals('#FF0000', $lower['hex']); // Normalized to uppercase
        $this->assertEquals('#FF0000', $mixed['hex']); // Normalized to uppercase
    }

    public function test_edge_case_very_dark_colors(): void
    {
        // Very dark but not quite black
        $veryDark = $this->mapper->toName('#0A0A0A');
        $this->assertEquals('Black', $veryDark);

        // Dark blue should still be Navy or similar
        $darkBlue = $this->mapper->toName('#000050');
        $this->assertContains($darkBlue, ['Navy', 'Black']);
    }

    public function test_edge_case_very_light_colors(): void
    {
        // Very light but not quite white
        $veryLight = $this->mapper->toName('#F5F5F5');
        $this->assertContains($veryLight, ['White', 'Off-White', 'Light Gray', 'Ivory', 'Cream']);

        // Should map to a light color name
        $result = $this->mapper->toNameAndHex('#FAFAFA');
        $this->assertContains($result['name'], ['White', 'Off-White', 'Light Gray', 'Ivory', 'Cream']);
    }

    public function test_vivid_primary_colors(): void
    {
        // Pure primaries should map correctly
        $this->assertEquals('Red', $this->mapper->toName('#FF0000'));
        $this->assertEquals('Blue', $this->mapper->toName('#0000FF'));

        // Lime is the bright green in the palette
        $brightGreen = $this->mapper->toName('#00FF00');
        $this->assertContains($brightGreen, ['Lime', 'Green']);
    }

    public function test_pastel_colors(): void
    {
        // Light pink
        $lightPink = $this->mapper->toName('#FFB6C1');
        $this->assertContains($lightPink, ['Pink', 'Blush', 'Rose']);

        // Light blue/powder blue
        $lightBlue = $this->mapper->toName('#B0E0E6');
        $this->assertEquals('Powder Blue', $lightBlue);

        // Mint
        $mint = $this->mapper->toName('#98FF98');
        $this->assertEquals('Mint', $mint);
    }
}
