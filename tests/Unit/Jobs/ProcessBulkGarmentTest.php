<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessBulkGarment;
use App\Models\User;
use App\Services\WardrobeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessBulkGarmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_processes_temp_file_and_cleans_up(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('temp/test-bulk.jpg', 'fake image content');

        $user = User::factory()->create();

        $this->mock(WardrobeService::class, function ($mock) {
            $mock->shouldReceive('storeGarment')->once();
        });

        $job = new ProcessBulkGarment($user, 'temp/test-bulk.jpg', 'shirt.jpg', 'upper');
        $job->handle(app(WardrobeService::class));

        Storage::disk('local')->assertMissing('temp/test-bulk.jpg');
    }

    public function test_job_cleans_up_temp_file_on_failure(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('temp/test-fail.jpg', 'fake image content');

        $user = User::factory()->create();

        $this->mock(WardrobeService::class, function ($mock) {
            $mock->shouldReceive('storeGarment')
                ->once()
                ->andThrow(new \RuntimeException('Processing failed'));
        });

        $job = new ProcessBulkGarment($user, 'temp/test-fail.jpg', 'shirt.jpg', 'upper');

        try {
            $job->handle(app(WardrobeService::class));
        } catch (\RuntimeException) {
            // Expected
        }

        Storage::disk('local')->assertMissing('temp/test-fail.jpg');
    }

    public function test_job_rethrows_exception_for_queue_retry(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('temp/test-retry.jpg', 'fake image content');

        $user = User::factory()->create();

        $this->mock(WardrobeService::class, function ($mock) {
            $mock->shouldReceive('storeGarment')
                ->once()
                ->andThrow(new \RuntimeException('API error'));
        });

        $job = new ProcessBulkGarment($user, 'temp/test-retry.jpg', 'shirt.jpg', 'upper');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API error');

        $job->handle(app(WardrobeService::class));
    }

    public function test_job_skips_if_temp_file_not_found(): void
    {
        Storage::fake('local');
        // Don't create the file - it should not exist

        $user = User::factory()->create();

        $this->mock(WardrobeService::class, function ($mock) {
            $mock->shouldNotReceive('storeGarment');
        });

        $job = new ProcessBulkGarment($user, 'temp/nonexistent.jpg', 'shirt.jpg', 'upper');
        $job->handle(app(WardrobeService::class));

        // Should complete without error
        $this->assertTrue(true);
    }
}
