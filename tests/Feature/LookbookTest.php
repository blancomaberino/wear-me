<?php

namespace Tests\Feature;

use App\Models\Lookbook;
use App\Models\LookbookItem;
use App\Models\OutfitSuggestion;
use App\Models\TryOnResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LookbookTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_lookbooks(): void
    {
        $this->get(route('lookbooks.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_lookbooks_index(): void
    {
        $user = User::factory()->create();
        Lookbook::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->get(route('lookbooks.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Lookbooks/Index')
                ->has('lookbooks', 3)
            );
    }

    public function test_user_can_create_a_lookbook(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('lookbooks.store'), [
                'name' => 'Summer Collection',
                'description' => 'My favorite summer outfits',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lookbooks', [
            'user_id' => $user->id,
            'name' => 'Summer Collection',
            'description' => 'My favorite summer outfits',
        ]);
    }

    public function test_user_can_view_their_lookbook(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('lookbooks.show', $lookbook))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Lookbooks/Show')
                ->where('lookbook.id', $lookbook->id)
                ->where('lookbook.name', $lookbook->name)
            );
    }

    public function test_user_cannot_view_other_users_lookbook(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lookbook = Lookbook::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('lookbooks.show', $lookbook))
            ->assertForbidden();
    }

    public function test_user_can_delete_their_lookbook(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('lookbooks.destroy', $lookbook))
            ->assertRedirect();

        $this->assertDatabaseMissing('lookbooks', [
            'id' => $lookbook->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_lookbook(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lookbook = Lookbook::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->delete(route('lookbooks.destroy', $lookbook))
            ->assertForbidden();

        $this->assertDatabaseHas('lookbooks', [
            'id' => $lookbook->id,
        ]);
    }

    public function test_user_can_add_item_to_lookbook(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $tryOnResult = TryOnResult::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('lookbooks.items.add', $lookbook), [
                'itemable_type' => 'tryon_result',
                'itemable_id' => $tryOnResult->id,
                'note' => 'Great look',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lookbook_items', [
            'lookbook_id' => $lookbook->id,
            'itemable_type' => TryOnResult::class,
            'itemable_id' => $tryOnResult->id,
            'note' => 'Great look',
        ]);
    }

    public function test_user_can_remove_item_from_lookbook(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $item = LookbookItem::factory()->for($lookbook)->create();

        $this->actingAs($user)
            ->delete(route('lookbooks.items.remove', [$lookbook, $item]))
            ->assertRedirect();

        $this->assertDatabaseMissing('lookbook_items', [
            'id' => $item->id,
        ]);
    }

    public function test_user_can_reorder_items(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $item1 = LookbookItem::factory()->for($lookbook)->create(['sort_order' => 0]);
        $item2 = LookbookItem::factory()->for($lookbook)->create(['sort_order' => 1]);

        $this->actingAs($user)
            ->patch(route('lookbooks.reorder', $lookbook), [
                'item_ids' => [$item2->id, $item1->id],
            ])
            ->assertOk();

        $this->assertEquals(0, $item2->fresh()->sort_order);
        $this->assertEquals(1, $item1->fresh()->sort_order);
    }

    public function test_lookbook_index_renders_correct_inertia_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('lookbooks.index'))
            ->assertInertia(fn ($page) => $page->component('Lookbooks/Index'));
    }

    public function test_lookbook_show_includes_items_relationship(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $tryOn = TryOnResult::factory()->for($user)->create();
        LookbookItem::factory()->for($lookbook)->create([
            'itemable_type' => TryOnResult::class,
            'itemable_id' => $tryOn->id,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('lookbooks.show', $lookbook))
            ->assertInertia(fn ($page) => $page
                ->component('Lookbooks/Show')
                ->has('lookbook.items', 1)
            );
    }

    public function test_creating_lookbook_with_empty_name_fails_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('lookbooks.store'), [
                'name' => '',
                'description' => 'Some description',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_creating_lookbook_without_name_fails_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('lookbooks.store'), [
                'description' => 'Some description',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_lookbook_item_with_outfit_suggestion_type(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $suggestion = OutfitSuggestion::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('lookbooks.items.add', $lookbook), [
                'itemable_type' => 'outfit_suggestion',
                'itemable_id' => $suggestion->id,
                'note' => 'Love this outfit',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lookbook_items', [
            'lookbook_id' => $lookbook->id,
            'itemable_type' => OutfitSuggestion::class,
            'itemable_id' => $suggestion->id,
        ]);
    }

    public function test_lookbook_index_includes_items_count(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        LookbookItem::factory(3)->for($lookbook)->create();

        $this->actingAs($user)
            ->get(route('lookbooks.index'))
            ->assertInertia(fn ($page) => $page
                ->has('lookbooks', 1)
                ->where('lookbooks.0.items_count', 3)
            );
    }

    public function test_lookbook_show_items_are_ordered_by_sort_order(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();

        $item1 = LookbookItem::factory()->for($lookbook)->create(['sort_order' => 2]);
        $item2 = LookbookItem::factory()->for($lookbook)->create(['sort_order' => 0]);
        $item3 = LookbookItem::factory()->for($lookbook)->create(['sort_order' => 1]);

        $this->actingAs($user)
            ->get(route('lookbooks.show', $lookbook))
            ->assertInertia(fn ($page) => $page
                ->has('lookbook.items', 3)
                ->where('lookbook.items.0.id', $item2->id)   // sort_order 0
                ->where('lookbook.items.1.id', $item3->id)   // sort_order 1
                ->where('lookbook.items.2.id', $item1->id)   // sort_order 2
            );
    }

    public function test_reorder_with_empty_item_ids_fails_validation(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();

        $this->actingAs($user)
            ->patchJson(route('lookbooks.reorder', $lookbook), [
                'item_ids' => [],
            ])
            ->assertStatus(422);
    }

    public function test_user_cannot_add_item_to_other_users_lookbook(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lookbook = Lookbook::factory()->for($otherUser)->create();
        $tryOn = TryOnResult::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('lookbooks.items.add', $lookbook), [
                'itemable_type' => 'tryon_result',
                'itemable_id' => $tryOn->id,
            ])
            ->assertForbidden();
    }

    public function test_lookbook_update_changes_name_and_description(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $this->actingAs($user)
            ->patch(route('lookbooks.update', $lookbook), [
                'name' => 'Updated Name',
                'description' => 'Updated Description',
            ])
            ->assertRedirect();

        $lookbook->refresh();
        $this->assertEquals('Updated Name', $lookbook->name);
        $this->assertEquals('Updated Description', $lookbook->description);
    }

    public function test_user_only_sees_own_lookbooks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Lookbook::factory(2)->for($user)->create();
        Lookbook::factory(3)->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('lookbooks.index'))
            ->assertInertia(fn ($page) => $page->has('lookbooks', 2));
    }

    public function test_lookbook_update_can_toggle_is_public(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create([
            'is_public' => false,
        ]);

        $this->actingAs($user)
            ->patch(route('lookbooks.update', $lookbook), [
                'name' => $lookbook->name,
                'is_public' => true,
            ])
            ->assertRedirect();

        $this->assertTrue($lookbook->fresh()->is_public);
    }

    public function test_lookbook_update_can_set_cover_from_item(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $tryOnResult = TryOnResult::factory()->for($user)->create([
            'result_path' => 'results/test-cover.jpg',
        ]);
        $item = LookbookItem::factory()->for($lookbook)->create([
            'itemable_type' => TryOnResult::class,
            'itemable_id' => $tryOnResult->id,
        ]);

        $this->actingAs($user)
            ->patch(route('lookbooks.update', $lookbook), [
                'name' => $lookbook->name,
                'cover_item_id' => $item->id,
            ])
            ->assertRedirect();

        $lookbook->refresh();
        $this->assertEquals('results/test-cover.jpg', $lookbook->cover_image_path);
    }

    public function test_lookbook_update_rejects_item_from_other_lookbook(): void
    {
        $user = User::factory()->create();
        $lookbook = Lookbook::factory()->for($user)->create();
        $otherLookbook = Lookbook::factory()->for($user)->create();
        $item = LookbookItem::factory()->for($otherLookbook)->create();

        $this->actingAs($user)
            ->patch(route('lookbooks.update', $lookbook), [
                'name' => $lookbook->name,
                'cover_item_id' => $item->id,
            ])
            ->assertRedirect();

        // Cover should NOT be set since item doesn't belong to this lookbook
        $this->assertNull($lookbook->fresh()->cover_image_path);
    }

    public function test_user_cannot_update_other_users_lookbook(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $lookbook = Lookbook::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->patch(route('lookbooks.update', $lookbook), [
                'name' => 'Hacked',
            ])
            ->assertForbidden();
    }
}
