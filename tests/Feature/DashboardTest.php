<?php

namespace Tests\Feature;

use App\Enums\GarmentCategory;
use App\Enums\ProcessingStatus;
use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\OutfitSuggestion;
use App\Models\TryOnResult;
use App\Models\TryOnVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_dashboard_shows_wardrobe_stats(): void
    {
        $user = User::factory()->create();

        Garment::factory()->upper()->for($user)->create();
        Garment::factory()->upper()->for($user)->create();
        Garment::factory()->lower()->for($user)->create();
        Garment::factory()->dress()->for($user)->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('wardrobeStats.total', 4)
                ->where('wardrobeStats.upper', 2)
                ->where('wardrobeStats.lower', 1)
                ->where('wardrobeStats.dress', 1)
            );
    }

    public function test_dashboard_shows_recent_tryons(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnResult::factory()->completed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->has('recentTryOns', 1)
            );
    }

    public function test_dashboard_limits_recent_tryons_to_six(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnResult::factory(8)->completed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->has('recentTryOns', 6)
            );
    }

    public function test_dashboard_shows_counts(): void
    {
        $user = User::factory()->create();

        ModelImage::factory(3)->for($user)->create();
        $modelImage = $user->modelImages()->first();
        $garment = Garment::factory()->for($user)->create();

        TryOnVideo::factory(2)->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);
        OutfitSuggestion::factory(2)->saved()->for($user)->create();
        OutfitSuggestion::factory()->for($user)->create(); // not saved

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('modelImageCount', 3)
                ->where('videoCount', 2)
                ->where('savedSuggestionCount', 2)
            );
    }

    public function test_dashboard_does_not_show_other_users_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Garment::factory(3)->for($otherUser)->create();
        ModelImage::factory(2)->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('wardrobeStats.total', 0)
                ->where('modelImageCount', 0)
            );
    }
}
