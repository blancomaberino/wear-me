<?php

namespace Tests\Unit\Jobs;

use App\Contracts\TryOnProviderContract;
use App\Contracts\TryOnSubmission;
use App\Enums\ProcessingStatus;
use App\Jobs\PollProviderTask;
use App\Jobs\ProcessTryOn;
use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\TryOnResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class ProcessTryOnTest extends TestCase
{
    use RefreshDatabase;

    public function test_async_provider_dispatches_poll(): void
    {
        Queue::fake([PollProviderTask::class]);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create(['category' => 'upper']);

        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);
        $tryOnResult->garments()->attach([$garment->id => ['sort_order' => 0]]);

        $mockProvider = Mockery::mock(TryOnProviderContract::class);
        $mockProvider->shouldReceive('name')->andReturn('kling');
        $mockProvider->shouldReceive('submitTryOn')
            ->once()
            ->withArgs(function ($modelPath, $garments, $promptHint) {
                return is_array($garments) && count($garments) === 1;
            })
            ->andReturn(TryOnSubmission::async('kling-task-abc'));

        $job = new ProcessTryOn($tryOnResult);
        $job->handle($mockProvider);

        $tryOnResult->refresh();
        $this->assertEquals(ProcessingStatus::Processing, $tryOnResult->status);
        $this->assertEquals('kling-task-abc', $tryOnResult->provider_task_id);
        $this->assertEquals('kling', $tryOnResult->provider);

        Queue::assertPushed(PollProviderTask::class);
    }

    public function test_sync_provider_completes_immediately(): void
    {
        Queue::fake([PollProviderTask::class]);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create(['category' => 'upper']);

        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);
        $tryOnResult->garments()->attach([$garment->id => ['sort_order' => 0]]);

        $mockProvider = Mockery::mock(TryOnProviderContract::class);
        $mockProvider->shouldReceive('name')->andReturn('gemini');
        $mockProvider->shouldReceive('submitTryOn')
            ->once()
            ->withArgs(function ($modelPath, $garments, $promptHint) {
                return is_array($garments) && count($garments) === 1;
            })
            ->andReturn(TryOnSubmission::sync('tryon-results/test-result.jpg'));

        $job = new ProcessTryOn($tryOnResult);
        $job->handle($mockProvider);

        $tryOnResult->refresh();
        $this->assertEquals(ProcessingStatus::Completed, $tryOnResult->status);
        $this->assertEquals('tryon-results/test-result.jpg', $tryOnResult->result_path);
        $this->assertEquals('gemini', $tryOnResult->provider);

        Queue::assertNotPushed(PollProviderTask::class);
    }

    public function test_job_marks_failed_on_exception(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create(['category' => 'upper']);

        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);
        $tryOnResult->garments()->attach([$garment->id => ['sort_order' => 0]]);

        $mockProvider = Mockery::mock(TryOnProviderContract::class);
        $mockProvider->shouldReceive('name')->andReturn('kling');
        $mockProvider->shouldReceive('submitTryOn')
            ->once()
            ->andThrow(new \RuntimeException('API connection failed'));

        $job = new ProcessTryOn($tryOnResult);
        $job->handle($mockProvider);

        $tryOnResult->refresh();
        $this->assertEquals(ProcessingStatus::Failed, $tryOnResult->status);
        $this->assertEquals('API connection failed', $tryOnResult->error_message);
    }

    public function test_job_loads_multiple_garments_from_pivot(): void
    {
        Queue::fake([PollProviderTask::class]);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment1 = Garment::factory()->for($user)->create(['category' => 'upper']);
        $garment2 = Garment::factory()->for($user)->create(['category' => 'lower']);

        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment1->id,
            'status' => ProcessingStatus::Pending,
        ]);
        $tryOnResult->garments()->attach([
            $garment1->id => ['sort_order' => 0],
            $garment2->id => ['sort_order' => 1],
        ]);

        $mockProvider = Mockery::mock(TryOnProviderContract::class);
        $mockProvider->shouldReceive('name')->andReturn('gemini');
        $mockProvider->shouldReceive('submitTryOn')
            ->once()
            ->withArgs(function ($modelPath, $garments, $promptHint) {
                return is_array($garments)
                    && count($garments) === 2
                    && $garments[0]['category'] === 'upper'
                    && $garments[1]['category'] === 'lower';
            })
            ->andReturn(TryOnSubmission::sync('tryon-results/multi-result.jpg'));

        $job = new ProcessTryOn($tryOnResult);
        $job->handle($mockProvider);

        $tryOnResult->refresh();
        $this->assertEquals(ProcessingStatus::Completed, $tryOnResult->status);
    }

    public function test_job_falls_back_to_legacy_garment_id(): void
    {
        Queue::fake([PollProviderTask::class]);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create(['category' => 'dress']);

        // No pivot data — legacy record
        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);

        $mockProvider = Mockery::mock(TryOnProviderContract::class);
        $mockProvider->shouldReceive('name')->andReturn('gemini');
        $mockProvider->shouldReceive('submitTryOn')
            ->once()
            ->withArgs(function ($modelPath, $garments, $promptHint) {
                return is_array($garments) && count($garments) === 1;
            })
            ->andReturn(TryOnSubmission::sync('tryon-results/legacy-result.jpg'));

        $job = new ProcessTryOn($tryOnResult);
        $job->handle($mockProvider);

        $tryOnResult->refresh();
        $this->assertEquals(ProcessingStatus::Completed, $tryOnResult->status);
    }

    public function test_job_has_retry_limit(): void
    {
        $user = User::factory()->create();
        $tryOnResult = TryOnResult::factory()->for($user)->create();

        $job = new ProcessTryOn($tryOnResult);

        $this->assertEquals(3, $job->tries);
    }
}
