<?php

namespace Tests\Unit\Enums;

use App\Enums\GarmentCategory;
use PHPUnit\Framework\TestCase;

class GarmentCategoryTest extends TestCase
{
    public function test_has_expected_cases(): void
    {
        $cases = GarmentCategory::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(GarmentCategory::Upper, $cases);
        $this->assertContains(GarmentCategory::Lower, $cases);
        $this->assertContains(GarmentCategory::Dress, $cases);
    }

    public function test_values_are_lowercase_strings(): void
    {
        $this->assertEquals('upper', GarmentCategory::Upper->value);
        $this->assertEquals('lower', GarmentCategory::Lower->value);
        $this->assertEquals('dress', GarmentCategory::Dress->value);
    }

    public function test_can_be_created_from_string(): void
    {
        $this->assertEquals(GarmentCategory::Upper, GarmentCategory::from('upper'));
        $this->assertEquals(GarmentCategory::Lower, GarmentCategory::from('lower'));
        $this->assertEquals(GarmentCategory::Dress, GarmentCategory::from('dress'));
    }

    public function test_tryFrom_returns_null_for_invalid(): void
    {
        $this->assertNull(GarmentCategory::tryFrom('invalid'));
        $this->assertNull(GarmentCategory::tryFrom('pants'));
    }
}
