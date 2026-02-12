<?php

namespace Tests\Feature;

use App\Models\Lookbook;
use App\Models\Reaction;
use App\Models\ShareLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_share_links(): void
    {
        $this->post(route('share.store'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_a_share_link_for_a_lookbook(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->postJson(route('share.store'), [
                'shareable_type' => 'lookbook',
                'shareable_id' => $lookbook->id,
                'expires_in' => '7_days',
            ])
            ->assertOk();

        $this->assertDatabaseHas('share_links', [
            'user_id' => $user->id,
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
            'is_active' => true,
        ]);
    }

    public function test_user_can_view_their_share_links(): void
    {
        $user = User::factory()->create();
        ShareLink::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->getJson(route('share.index'))
            ->assertOk()
            ->assertJsonStructure([
                'links' => [
                    '*' => ['token', 'shareable_type', 'shareable_id']
                ]
            ]);
    }

    public function test_user_can_delete_their_share_link(): void
    {
        $user = User::factory()->create();
        $shareLink = ShareLink::factory()->for($user)->create();

        $this->actingAs($user)
            ->deleteJson(route('share.destroy', $shareLink))
            ->assertOk();

        $this->assertDatabaseHas('share_links', [
            'id' => $shareLink->id,
            'is_active' => false,
        ]);
    }

    public function test_public_can_view_shared_content_via_token(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $shareLink = ShareLink::factory()->for($user)->create([
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
            'is_active' => true,
        ]);

        $this->get(route('share.public', $shareLink->token))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/SharedView')
                ->where('shareLink.token', $shareLink->token)
            );
    }

    public function test_public_can_react_to_shared_content(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $shareLink = ShareLink::factory()->for($user)->create([
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
            'is_active' => true,
        ]);

        $this->postJson(route('share.react', $shareLink->token), [
            'type' => 'heart',
        ])
            ->assertOk();

        $this->assertDatabaseHas('reactions', [
            'share_link_id' => $shareLink->id,
            'type' => 'heart',
        ]);
    }

    public function test_expired_share_links_return_404(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $shareLink = ShareLink::factory()->expired()->for($user)->create([
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
        ]);

        $this->get(route('share.public', $shareLink->token))
            ->assertNotFound();
    }

    public function test_user_cannot_share_other_users_content(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lookbook = Lookbook::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->postJson(route('share.store'), [
                'shareable_type' => 'lookbook',
                'shareable_id' => $lookbook->id,
                'expires_in' => '7_days',
            ])
            ->assertNotFound();
    }

    public function test_share_token_is_unique_and_sufficient_length(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();

        $this->actingAs($user)
            ->postJson(route('share.store'), [
                'shareable_type' => 'lookbook',
                'shareable_id' => $lookbook->id,
            ]);

        $link = ShareLink::where('user_id', $user->id)->first();
        $this->assertNotNull($link);
        $this->assertGreaterThanOrEqual(32, strlen($link->token));

        // Create a second link and verify tokens differ
        $this->actingAs($user)
            ->postJson(route('share.store'), [
                'shareable_type' => 'lookbook',
                'shareable_id' => $lookbook->id,
            ]);

        $links = ShareLink::where('user_id', $user->id)->get();
        $tokens = $links->pluck('token')->toArray();
        $this->assertCount(2, array_unique($tokens));
    }

    public function test_view_count_increments_on_public_view(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $shareLink = ShareLink::factory()->for($user)->create([
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
            'is_active' => true,
            'view_count' => 0,
        ]);

        $this->get(route('share.public', $shareLink->token))->assertOk();
        $this->assertEquals(1, $shareLink->fresh()->view_count);

        $this->get(route('share.public', $shareLink->token))->assertOk();
        $this->assertEquals(2, $shareLink->fresh()->view_count);
    }

    public function test_reaction_deduplication_by_visitor_hash(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $shareLink = ShareLink::factory()->for($user)->create([
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
            'is_active' => true,
        ]);

        // First reaction should succeed
        $response1 = $this->postJson(route('share.react', $shareLink->token), [
            'type' => 'heart',
        ]);
        $response1->assertOk()->assertJson(['added' => true]);

        // Same visitor (same IP + UA) should be deduplicated
        $response2 = $this->postJson(route('share.react', $shareLink->token), [
            'type' => 'heart',
        ]);
        $response2->assertOk()->assertJson(['added' => false]);

        // Only one reaction should exist
        $this->assertEquals(1, $shareLink->reactions()->count());
    }

    public function test_inactive_share_link_returns_404(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $shareLink = ShareLink::factory()->inactive()->for($user)->create([
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
        ]);

        $this->get(route('share.public', $shareLink->token))
            ->assertNotFound();
    }

    public function test_share_link_creation_requires_valid_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('share.store'), [
                'shareable_type' => 'invalid_type',
                'shareable_id' => 1,
            ])
            ->assertStatus(422);
    }

    public function test_public_view_returns_correct_inertia_component_and_content(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create(['name' => 'My Style']);
        $shareLink = ShareLink::factory()->for($user)->create([
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
            'is_active' => true,
        ]);

        $this->get(route('share.public', $shareLink->token))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/SharedView')
                ->where('shareLink.token', $shareLink->token)
                ->where('shareLink.shareable_type', 'Lookbook')
                ->has('content')
            );
    }

    public function test_reaction_requires_valid_type(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $shareLink = ShareLink::factory()->for($user)->create([
            'shareable_type' => Lookbook::class,
            'shareable_id' => $lookbook->id,
            'is_active' => true,
        ]);

        $this->postJson(route('share.react', $shareLink->token), [
            'type' => 'invalid_emoji',
        ])
            ->assertStatus(422);
    }

    public function test_user_cannot_delete_other_users_share_link(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $shareLink = ShareLink::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->deleteJson(route('share.destroy', $shareLink))
            ->assertForbidden();

        $this->assertTrue($shareLink->fresh()->is_active);
    }
}
