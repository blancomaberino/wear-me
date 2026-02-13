<?php

namespace Tests\Feature;

use App\Enums\ProcessingStatus;
use App\Jobs\ProcessTryOn;
use App\Models\Garment;
use App\Models\ModelImage;
use App\Models\TryOnResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TryOnTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_tryon(): void
    {
        $this->get(route('tryon.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_tryon_workspace(): void
    {
        $user = User::factory()->create();
        ModelImage::factory(2)->for($user)->create();
        Garment::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->get(route('tryon.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('TryOn/Index')
                ->has('modelImages', 2)
                ->has('garments', 3)
            );
    }

    public function test_user_can_initiate_tryon(): void
    {
        Queue::fake();

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

        $this->assertDatabaseHas('tryon_result_garment', [
            'garment_id' => $garment->id,
            'sort_order' => 0,
        ]);

        Queue::assertPushed(ProcessTryOn::class);
    }

    public function test_user_can_initiate_multi_garment_tryon(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment1 = Garment::factory()->for($user)->create(['category' => 'upper']);
        $garment2 = Garment::factory()->for($user)->create(['category' => 'lower']);

        $this->actingAs($user)
            ->post(route('tryon.store'), [
                'model_image_id' => $modelImage->id,
                'garment_ids' => [$garment1->id, $garment2->id],
            ])
            ->assertRedirect();

        $result = TryOnResult::latest()->first();
        $this->assertEquals($garment1->id, $result->garment_id);
        $this->assertCount(2, $result->garments);
        $this->assertEquals(0, $result->garments->first()->pivot->sort_order);
        $this->assertEquals(1, $result->garments->last()->pivot->sort_order);

        Queue::assertPushed(ProcessTryOn::class);
    }

    public function test_tryon_backward_compat_garment_id(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('tryon.store'), [
                'model_image_id' => $modelImage->id,
                'garment_id' => $garment->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tryon_results', [
            'user_id' => $user->id,
            'garment_id' => $garment->id,
        ]);

        Queue::assertPushed(ProcessTryOn::class);
    }

    public function test_tryon_requires_valid_model_image_and_garments(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tryon.store'), [])
            ->assertSessionHasErrors(['model_image_id', 'garment_ids']);
    }

    public function test_tryon_rejects_nonexistent_ids(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tryon.store'), [
                'model_image_id' => 999,
                'garment_ids' => [999],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['model_image_id', 'garment_ids.0']);
    }

    public function test_user_cannot_use_other_users_model_image(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $modelImage = ModelImage::factory()->for($otherUser)->create();
        $garment = Garment::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('tryon.store'), [
                'model_image_id' => $modelImage->id,
                'garment_ids' => [$garment->id],
            ])
            ->assertStatus(404);
    }

    public function test_tryon_rejects_more_than_five_garments(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garments = Garment::factory(6)->for($user)->create();

        $this->actingAs($user)
            ->post(route('tryon.store'), [
                'model_image_id' => $modelImage->id,
                'garment_ids' => $garments->pluck('id')->all(),
            ])
            ->assertSessionHasErrors(['garment_ids']);
    }

    public function test_user_can_view_tryon_result(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        $result = TryOnResult::factory()->completed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('tryon.show', $result))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('TryOn/Result')
                ->has('tryOnResult')
                ->where('tryOnResult.id', $result->id)
                ->where('tryOnResult.status', 'completed')
            );
    }

    public function test_user_cannot_view_other_users_result(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $modelImage = ModelImage::factory()->for($otherUser)->create();
        $garment = Garment::factory()->for($otherUser)->create();

        $result = TryOnResult::factory()->for($otherUser)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('tryon.show', $result))
            ->assertForbidden();
    }

    public function test_user_can_check_tryon_status(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        $result = TryOnResult::factory()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
            'status' => ProcessingStatus::Processing,
        ]);

        $this->actingAs($user)
            ->getJson(route('tryon.status', $result))
            ->assertOk()
            ->assertJson([
                'status' => 'processing',
                'result_url' => null,
                'error_message' => null,
            ]);
    }

    public function test_user_cannot_check_other_users_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $result = TryOnResult::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->getJson(route('tryon.status', $result))
            ->assertForbidden();
    }

    public function test_user_can_view_tryon_history(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnResult::factory(5)->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('tryon.history'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('TryOn/History')
                ->has('results.data', 5)
            );
    }

    public function test_tryon_history_is_paginated(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        TryOnResult::factory(15)->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('tryon.history'))
            ->assertInertia(fn ($page) => $page
                ->has('results.data', 12)
            );
    }

    public function test_user_can_toggle_favorite(): void
    {
        $user = User::factory()->create();
        $result = TryOnResult::factory()->for($user)->create([
            'is_favorite' => false,
        ]);

        $this->actingAs($user)
            ->patch(route('tryon.favorite', $result))
            ->assertRedirect();

        $this->assertTrue($result->fresh()->is_favorite);

        $this->actingAs($user)
            ->patch(route('tryon.favorite', $result))
            ->assertRedirect();

        $this->assertFalse($result->fresh()->is_favorite);
    }

    public function test_user_cannot_toggle_favorite_on_other_users_result(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $result = TryOnResult::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->patch(route('tryon.favorite', $result))
            ->assertForbidden();
    }
}
