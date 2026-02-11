<?php

namespace Tests\Unit\Models;

use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\OutfitSuggestion;
use App\Models\TryOnResult;
use App\Models\TryOnVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_model_images(): void
    {
        $user = User::factory()->create();
        ModelImage::factory(3)->for($user)->create();

        $this->assertCount(3, $user->modelImages);
        $this->assertInstanceOf(ModelImage::class, $user->modelImages->first());
    }

    public function test_user_has_many_garments(): void
    {
        $user = User::factory()->create();
        Garment::factory(2)->for($user)->create();

        $this->assertCount(2, $user->garments);
        $this->assertInstanceOf(Garment::class, $user->garments->first());
    }

    public function test_user_has_many_tryon_results(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnResult::factory(2)->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->assertCount(2, $user->tryonResults);
        $this->assertInstanceOf(TryOnResult::class, $user->tryonResults->first());
    }

    public function test_user_has_many_tryon_videos(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnVideo::factory(2)->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->assertCount(2, $user->tryonVideos);
        $this->assertInstanceOf(TryOnVideo::class, $user->tryonVideos->first());
    }

    public function test_user_has_many_outfit_suggestions(): void
    {
        $user = User::factory()->create();
        OutfitSuggestion::factory(2)->for($user)->create();

        $this->assertCount(2, $user->outfitSuggestions);
        $this->assertInstanceOf(OutfitSuggestion::class, $user->outfitSuggestions->first());
    }

    public function test_user_fillable_includes_google_fields(): void
    {
        $user = User::factory()->create([
            'google_id' => 'google-123',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $this->assertEquals('google-123', $user->google_id);
        $this->assertEquals('https://example.com/avatar.jpg', $user->avatar);
    }
}
