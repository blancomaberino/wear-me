<?php

namespace Tests\Feature;

use App\Enums\ProcessingStatus;
use App\Jobs\ProcessTryOnVideo;
use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\TryOnResult;
use App\Models\TryOnVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TryOnVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_videos(): void
    {
        $this->get(route('videos.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_video_index(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnResult::factory(2)->completed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('videos.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Videos/Index')
                ->has('tryOnResults', 2)
            );
    }

    public function test_video_index_only_shows_completed_tryons(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnResult::factory()->completed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);
        TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending,
        ]);
        TryOnResult::factory()->failed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('videos.index'))
            ->assertInertia(fn ($page) => $page
                ->has('tryOnResults', 1)
            );
    }

    public function test_user_can_generate_video(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        $tryOnResult = TryOnResult::factory()->completed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->post(route('videos.store'), [
                'tryon_result_id' => $tryOnResult->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tryon_videos', [
            'user_id' => $user->id,
            'tryon_result_id' => $tryOnResult->id,
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Pending->value,
        ]);

        Queue::assertPushed(ProcessTryOnVideo::class);
    }

    public function test_video_generation_requires_valid_tryon_result(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('videos.store'), [])
            ->assertSessionHasErrors('tryon_result_id');
    }

    public function test_user_cannot_generate_video_from_other_users_tryon(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $result = TryOnResult::factory()->completed()->for($otherUser)->create();

        $this->actingAs($user)
            ->post(route('videos.store'), [
                'tryon_result_id' => $result->id,
            ])
            ->assertStatus(404);
    }

    public function test_user_can_view_video(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();
        $tryOnResult = TryOnResult::factory()->completed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $video = TryOnVideo::factory()->completed()->for($user)->create([
            'tryon_result_id' => $tryOnResult->id,
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('videos.show', $video))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Videos/Show')
                ->has('video')
                ->where('video.id', $video->id)
                ->where('video.status', 'completed')
            );
    }

    public function test_user_cannot_view_other_users_video(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $video = TryOnVideo::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('videos.show', $video))
            ->assertForbidden();
    }

    public function test_user_can_check_video_status(): void
    {
        $user = User::factory()->create();
        $video = TryOnVideo::factory()->for($user)->create([
            'status' => ProcessingStatus::Processing,
        ]);

        $this->actingAs($user)
            ->getJson(route('videos.status', $video))
            ->assertOk()
            ->assertJson([
                'status' => 'processing',
                'video_url' => null,
                'error_message' => null,
            ]);
    }

    public function test_user_cannot_check_other_users_video_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $video = TryOnVideo::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->getJson(route('videos.status', $video))
            ->assertForbidden();
    }

    public function test_user_can_view_video_history(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnVideo::factory(5)->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('videos.history'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Videos/History')
                ->has('videos.data', 5)
            );
    }

    public function test_video_history_is_paginated(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnVideo::factory(15)->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('videos.history'))
            ->assertInertia(fn ($page) => $page
                ->has('videos.data', 12)
            );
    }
}
