<?php

namespace Tests\Feature;

use App\Contracts\TryOnProviderContract;
use App\Contracts\TryOnSubmission;
use App\Enums\ProcessingStatus;
use App\Jobs\PollProviderTask;
use App\Jobs\ProcessTryOn;
use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\TryOnResult;
use App\Models\User;
use App\Services\TryOn\GeminiTryOnProvider;
use App\Services\TryOn\KlingTryOnProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TryOnProviderE2ETest extends TestCase
{
    use RefreshDatabase;

    // ─── Provider Resolution ────────────────────────────────────

    public function test_gemini_provider_resolves_when_configured(): void
    {
        config(['services.tryon.provider' => 'gemini']);

        $provider = app(TryOnProviderContract::class);

        $this->assertInstanceOf(GeminiTryOnProvider::class, $provider);
        $this->assertTrue($provider->isSynchronous());
        $this->assertEquals('gemini', $provider->name());
    }

    public function test_kling_provider_resolves_when_configured(): void
    {
        config(['services.tryon.provider' => 'kling']);

        $provider = app(TryOnProviderContract::class);

        $this->assertInstanceOf(KlingTryOnProvider::class, $provider);
        $this->assertFalse($provider->isSynchronous());
        $this->assertEquals('kling', $provider->name());
    }

    public function test_kling_provider_resolves_by_default(): void
    {
        config(['services.tryon.provider' => null]);

        $provider = app(TryOnProviderContract::class);

        $this->assertInstanceOf(KlingTryOnProvider::class, $provider);
    }

    // ─── Gemini Sync Flow (mocked HTTP) ─────────────────────────

    public function test_gemini_full_flow_completes_immediately(): void
    {
        Queue::fake([PollProviderTask::class]);
        config(['services.tryon.provider' => 'gemini']);

        Storage::disk('public')->put('model-images/test-human.jpg', file_get_contents(
            base_path('tests/Fixtures/test-image.jpg')
        ));
        Storage::disk('public')->put('garments/test-garment.jpg', file_get_contents(
            base_path('tests/Fixtures/test-image.jpg')
        ));

        $fakeImageBase64 = base64_encode(file_get_contents(base_path('tests/Fixtures/test-image.jpg')));

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'inline_data' => [
                                        'mime_type' => 'image/jpeg',
                                        'data' => $fakeImageBase64,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create([
            'path' => 'model-images/test-human.jpg',
        ]);
        $garment = Garment::factory()->for($user)->create([
            'path' => 'garments/test-garment.jpg',
        ]);

        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);
        $tryOnResult->garments()->attach([$garment->id => ['sort_order' => 0]]);

        $provider = app(TryOnProviderContract::class);
        $job = new ProcessTryOn($tryOnResult);
        $job->handle($provider);

        $tryOnResult->refresh();

        $this->assertEquals(ProcessingStatus::Completed, $tryOnResult->status);
        $this->assertEquals('gemini', $tryOnResult->provider);
        $this->assertNotNull($tryOnResult->result_path);
        $this->assertStringStartsWith('tryon-results/', $tryOnResult->result_path);
        $this->assertTrue(Storage::disk('public')->exists($tryOnResult->result_path));

        Queue::assertNotPushed(PollProviderTask::class);

        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com')
                && str_contains($request->url(), 'generateContent');
        });
    }

    public function test_gemini_flow_marks_failed_on_api_error(): void
    {
        config(['services.tryon.provider' => 'gemini']);

        Storage::disk('public')->put('model-images/test-human.jpg', file_get_contents(
            base_path('tests/Fixtures/test-image.jpg')
        ));
        Storage::disk('public')->put('garments/test-garment.jpg', file_get_contents(
            base_path('tests/Fixtures/test-image.jpg')
        ));

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'error' => ['message' => 'Invalid API key', 'code' => 403],
            ], 403),
        ]);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create([
            'path' => 'model-images/test-human.jpg',
        ]);
        $garment = Garment::factory()->for($user)->create([
            'path' => 'garments/test-garment.jpg',
        ]);

        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);
        $tryOnResult->garments()->attach([$garment->id => ['sort_order' => 0]]);

        $provider = app(TryOnProviderContract::class);
        $job = new ProcessTryOn($tryOnResult);
        $job->handle($provider);

        $tryOnResult->refresh();

        $this->assertEquals(ProcessingStatus::Failed, $tryOnResult->status);
        $this->assertStringStartsWith('error.', $tryOnResult->error_message);
    }

    public function test_gemini_flow_marks_failed_on_empty_response(): void
    {
        config(['services.tryon.provider' => 'gemini']);

        Storage::disk('public')->put('model-images/test-human.jpg', file_get_contents(
            base_path('tests/Fixtures/test-image.jpg')
        ));
        Storage::disk('public')->put('garments/test-garment.jpg', file_get_contents(
            base_path('tests/Fixtures/test-image.jpg')
        ));

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'I cannot generate that image.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create([
            'path' => 'model-images/test-human.jpg',
        ]);
        $garment = Garment::factory()->for($user)->create([
            'path' => 'garments/test-garment.jpg',
        ]);

        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);
        $tryOnResult->garments()->attach([$garment->id => ['sort_order' => 0]]);

        $provider = app(TryOnProviderContract::class);
        $job = new ProcessTryOn($tryOnResult);
        $job->handle($provider);

        $tryOnResult->refresh();

        $this->assertEquals(ProcessingStatus::Failed, $tryOnResult->status);
        $this->assertStringStartsWith('error.', $tryOnResult->error_message);
    }

    // ─── Full HTTP E2E (store endpoint → job) ───────────────────

    public function test_store_endpoint_with_gemini_dispatches_job(): void
    {
        Queue::fake([ProcessTryOn::class]);
        config(['services.tryon.provider' => 'gemini']);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('tryon.store'), [
                'model_image_id' => $modelImage->id,
                'garment_ids' => [$garment->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tryon_results', [
            'user_id' => $user->id,
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending->value,
        ]);

        Queue::assertPushed(ProcessTryOn::class);
    }

    // ─── Kling Async Flow (regression) ──────────────────────────

    public function test_kling_flow_dispatches_poll_job(): void
    {
        Queue::fake([PollProviderTask::class]);
        config([
            'services.tryon.provider' => 'kling',
            'services.kling.access_key' => 'test-access-key-12345678',
            'services.kling.secret_key' => 'test-secret-key-that-is-long-enough-for-hs256-algorithm',
        ]);

        Storage::disk('public')->put('model-images/test-human.jpg', file_get_contents(
            base_path('tests/Fixtures/test-image.jpg')
        ));
        Storage::disk('public')->put('garments/test-garment.jpg', file_get_contents(
            base_path('tests/Fixtures/test-image.jpg')
        ));

        Http::fake([
            'api.klingai.com/*' => Http::response([
                'data' => [
                    'task_id' => 'kling-e2e-task-123',
                    'task_status' => 'submitted',
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create([
            'path' => 'model-images/test-human.jpg',
        ]);
        $garment = Garment::factory()->for($user)->create([
            'path' => 'garments/test-garment.jpg',
        ]);

        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);
        $tryOnResult->garments()->attach([$garment->id => ['sort_order' => 0]]);

        $provider = app(TryOnProviderContract::class);
        $job = new ProcessTryOn($tryOnResult);
        $job->handle($provider);

        $tryOnResult->refresh();

        $this->assertEquals(ProcessingStatus::Processing, $tryOnResult->status);
        $this->assertEquals('kling', $tryOnResult->provider);
        $this->assertEquals('kling-e2e-task-123', $tryOnResult->provider_task_id);

        Queue::assertPushed(PollProviderTask::class);
    }

    // ─── Live Gemini API Test ───────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Group('live-api')]
    public function test_live_gemini_api_returns_image(): void
    {
        $apiKey = config('services.gemini.api_key');

        if (empty($apiKey)) {
            $this->markTestSkipped('GEMINI_API_KEY not configured.');
        }

        $modelPath = 'model-images/rfMqxOYuDKKPzHyVYaNWQ9HD58kWLnbuEgCVABzj.jpg';
        $garmentPath = 'garments/n37q452GVkYhba4ui8p2kZdBXS6gSFyBi17HKG17.jpg';

        if (!Storage::disk('public')->exists($modelPath) || !Storage::disk('public')->exists($garmentPath)) {
            $this->markTestSkipped('Test images not found in storage.');
        }

        $provider = new GeminiTryOnProvider();
        $submission = $provider->submitTryOn($modelPath, [
            ['path' => $garmentPath, 'category' => 'upper'],
        ]);

        $this->assertTrue($submission->isComplete);
        $this->assertNull($submission->taskId);
        $this->assertNotNull($submission->resultPath);
        $this->assertStringStartsWith('tryon-results/', $submission->resultPath);
        $this->assertTrue(Storage::disk('public')->exists($submission->resultPath));

        $fileSize = Storage::disk('public')->size($submission->resultPath);
        $this->assertGreaterThan(1000, $fileSize, 'Result image should be a real image (>1KB)');
    }
}
