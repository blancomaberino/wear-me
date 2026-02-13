<?php

namespace Tests\Unit\Jobs;

use App\Jobs\BackfillGarmentColors;
use App\Models\Garment;
use App\Models\User;
use App\Services\GarmentColorExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillGarmentColorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_processes_garments_without_color_tags(): void
    {
        $user = User::factory()->create();
        $garment1 = Garment::factory()->for($user)->create(['color_tags' => null]);
        $garment2 = Garment::factory()->for($user)->create(['color_tags' => null]);

        $this->mock(GarmentColorExtractor::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->twice()
                ->andReturn([['hex' => '#FF0000', 'name' => 'Red']]);
        });

        $job = new BackfillGarmentColors($user);
        $job->handle(app(GarmentColorExtractor::class));

        $garment1->refresh();
        $garment2->refresh();

        $this->assertNotNull($garment1->color_tags);
        $this->assertNotNull($garment2->color_tags);
        $this->assertEquals([['hex' => '#FF0000', 'name' => 'Red']], $garment1->color_tags);
        $this->assertEquals([['hex' => '#FF0000', 'name' => 'Red']], $garment2->color_tags);
    }

    public function test_job_skips_garments_with_existing_color_tags(): void
    {
        $user = User::factory()->create();
        // Factory default includes color_tags
        Garment::factory()->for($user)->count(2)->create();

        $this->mock(GarmentColorExtractor::class, function ($mock) {
            $mock->shouldNotReceive('extract');
        });

        $job = new BackfillGarmentColors($user);
        $job->handle(app(GarmentColorExtractor::class));

        // If we get here without exception, the mock verified extract was not called
        $this->assertTrue(true);
    }

    public function test_job_handles_extraction_failure_gracefully(): void
    {
        $user = User::factory()->create();
        $garment1 = Garment::factory()->for($user)->create(['color_tags' => null]);
        $garment2 = Garment::factory()->for($user)->create(['color_tags' => null]);

        $this->mock(GarmentColorExtractor::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->ordered()
                ->andThrow(new \Exception('Extraction failed'));

            $mock->shouldReceive('extract')
                ->once()
                ->ordered()
                ->andReturn([['hex' => '#00FF00', 'name' => 'Green']]);
        });

        $job = new BackfillGarmentColors($user);

        // Job should not throw
        $job->handle(app(GarmentColorExtractor::class));

        $garment1->refresh();
        $garment2->refresh();

        // One garment should have colors, one should remain null
        $garments = [$garment1, $garment2];
        $withColors = collect($garments)->filter(fn($g) => $g->color_tags !== null)->count();
        $withoutColors = collect($garments)->filter(fn($g) => $g->color_tags === null)->count();

        $this->assertEquals(1, $withColors);
        $this->assertEquals(1, $withoutColors);
    }

    public function test_job_handles_empty_extraction_result(): void
    {
        $user = User::factory()->create();
        $garment = Garment::factory()->for($user)->create(['color_tags' => null]);

        $this->mock(GarmentColorExtractor::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn([]);
        });

        $job = new BackfillGarmentColors($user);
        $job->handle(app(GarmentColorExtractor::class));

        $garment->refresh();

        $this->assertNull($garment->color_tags);
    }

    public function test_job_only_processes_own_users_garments(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1Garment = Garment::factory()->for($user1)->create(['color_tags' => null]);
        $user2Garment = Garment::factory()->for($user2)->create(['color_tags' => null]);

        $this->mock(GarmentColorExtractor::class, function ($mock) {
            $mock->shouldReceive('extract')
                ->once()
                ->andReturn([['hex' => '#0000FF', 'name' => 'Blue']]);
        });

        $job = new BackfillGarmentColors($user1);
        $job->handle(app(GarmentColorExtractor::class));

        $user1Garment->refresh();
        $user2Garment->refresh();

        $this->assertNotNull($user1Garment->color_tags);
        $this->assertNull($user2Garment->color_tags);
    }

    public function test_job_has_correct_timeout_and_tries(): void
    {
        $user = User::factory()->create();
        $job = new BackfillGarmentColors($user);

        $this->assertEquals(300, $job->timeout);
        $this->assertEquals(1, $job->tries);
    }
}
