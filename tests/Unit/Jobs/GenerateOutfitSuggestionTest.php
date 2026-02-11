<?php

namespace Tests\Unit\Jobs;

use App\Jobs\GenerateOutfitSuggestion;
use App\Models\OutfitSuggestion;
use App\Models\User;
use App\Services\OutfitSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GenerateOutfitSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_suggestions_from_service(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OutfitSuggestionService::class);
        $mockService->shouldReceive('generateSuggestions')
            ->with($user, 'casual')
            ->once()
            ->andReturn([
                ['garment_ids' => [1, 2], 'suggestion' => 'First outfit'],
                ['garment_ids' => [3, 4], 'suggestion' => 'Second outfit'],
            ]);

        $job = new GenerateOutfitSuggestion($user, 'casual');
        $job->handle($mockService);

        $this->assertEquals(2, OutfitSuggestion::count());

        $this->assertDatabaseHas('outfit_suggestions', [
            'user_id' => $user->id,
            'suggestion_text' => 'First outfit',
            'occasion' => 'casual',
        ]);

        $this->assertDatabaseHas('outfit_suggestions', [
            'user_id' => $user->id,
            'suggestion_text' => 'Second outfit',
            'occasion' => 'casual',
        ]);
    }

    public function test_job_handles_exception_gracefully(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OutfitSuggestionService::class);
        $mockService->shouldReceive('generateSuggestions')
            ->once()
            ->andThrow(new \RuntimeException('API failed'));

        $job = new GenerateOutfitSuggestion($user, 'evening');
        $job->handle($mockService);

        $this->assertEquals(0, OutfitSuggestion::count());
    }

    public function test_job_uses_correct_occasion(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OutfitSuggestionService::class);
        $mockService->shouldReceive('generateSuggestions')
            ->with($user, 'work')
            ->once()
            ->andReturn([
                ['garment_ids' => [1], 'suggestion' => 'Work outfit'],
            ]);

        $job = new GenerateOutfitSuggestion($user, 'work');
        $job->handle($mockService);

        $this->assertDatabaseHas('outfit_suggestions', [
            'occasion' => 'work',
        ]);
    }

    public function test_job_has_retry_limit(): void
    {
        $user = User::factory()->create();
        $job = new GenerateOutfitSuggestion($user);

        $this->assertEquals(2, $job->tries);
    }

    public function test_job_handles_empty_suggestions(): void
    {
        $user = User::factory()->create();

        $mockService = Mockery::mock(OutfitSuggestionService::class);
        $mockService->shouldReceive('generateSuggestions')
            ->once()
            ->andReturn([]);

        $job = new GenerateOutfitSuggestion($user, 'sport');
        $job->handle($mockService);

        $this->assertEquals(0, OutfitSuggestion::count());
    }
}
