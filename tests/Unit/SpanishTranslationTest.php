<?php

namespace Tests\Unit;

use Tests\TestCase;

class SpanishTranslationTest extends TestCase
{
    public function test_outfits_spanish_has_correct_diacritics(): void
    {
        $outfitsPath = resource_path('js/i18n/locales/es/outfits.ts');
        $this->assertFileExists($outfitsPath);

        $content = file_get_contents($outfitsPath);

        // Should use todavía (with accent), not todavia
        $this->assertStringContainsString('todavía', $content, 'Missing accent on todavía in es/outfits.ts');

        // Should use Puntuación de armonía (with accents)
        $this->assertStringContainsString('Puntuación', $content, 'Missing accent on Puntuación in es/outfits.ts');
        $this->assertStringContainsString('armonía', $content, 'Missing accent on armonía in es/outfits.ts');
    }

    public function test_share_spanish_has_correct_diacritics(): void
    {
        $sharePath = resource_path('js/i18n/locales/es/share.ts');
        $this->assertFileExists($sharePath);

        $content = file_get_contents($sharePath);

        // Should use ¡Copiado! with inverted exclamation
        $this->assertStringContainsString('¡Copiado!', $content, 'Missing inverted exclamation in es/share.ts');

        // Should use día (with accent), not dia
        $this->assertStringContainsString('día', $content, 'Missing accent on día in es/share.ts');

        // Should NOT have 'dia' without accent (except as part of 'día')
        // Check that all occurrences of 'dia' are actually 'día'
        $this->assertStringNotContainsString("'1 dia'", $content, 'Found unaccented dia in es/share.ts');
        $this->assertStringNotContainsString("'7 dias'", $content, 'Found unaccented dias in es/share.ts');
        $this->assertStringNotContainsString("'30 dias'", $content, 'Found unaccented dias in es/share.ts');
    }

    public function test_all_spanish_locale_files_exist(): void
    {
        $localeDir = resource_path('js/i18n/locales/es');
        $expectedFiles = ['common.ts', 'wardrobe.ts', 'tryon.ts', 'outfits.ts', 'lookbooks.ts', 'share.ts', 'processing.ts', 'packing.ts', 'export.ts', 'nav.ts'];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists("{$localeDir}/{$file}", "Missing Spanish locale file: {$file}");
        }
    }
}
