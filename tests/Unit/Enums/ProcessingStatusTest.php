<?php

namespace Tests\Unit\Enums;

use App\Enums\ProcessingStatus;
use PHPUnit\Framework\TestCase;

class ProcessingStatusTest extends TestCase
{
    public function test_has_expected_cases(): void
    {
        $cases = ProcessingStatus::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(ProcessingStatus::Pending, $cases);
        $this->assertContains(ProcessingStatus::Processing, $cases);
        $this->assertContains(ProcessingStatus::Completed, $cases);
        $this->assertContains(ProcessingStatus::Failed, $cases);
    }

    public function test_values_are_lowercase_strings(): void
    {
        $this->assertEquals('pending', ProcessingStatus::Pending->value);
        $this->assertEquals('processing', ProcessingStatus::Processing->value);
        $this->assertEquals('completed', ProcessingStatus::Completed->value);
        $this->assertEquals('failed', ProcessingStatus::Failed->value);
    }

    public function test_can_be_created_from_string(): void
    {
        $this->assertEquals(ProcessingStatus::Pending, ProcessingStatus::from('pending'));
        $this->assertEquals(ProcessingStatus::Completed, ProcessingStatus::from('completed'));
    }

    public function test_tryFrom_returns_null_for_invalid(): void
    {
        $this->assertNull(ProcessingStatus::tryFrom('invalid'));
        $this->assertNull(ProcessingStatus::tryFrom('cancelled'));
    }
}
