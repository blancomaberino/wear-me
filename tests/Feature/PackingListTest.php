<?php

namespace Tests\Feature;

use App\Models\Garment;
use App\Models\PackingList;
use App\Models\PackingListItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_packing_lists(): void
    {
        $this->get(route('packing-lists.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_packing_lists_index(): void
    {
        $user = User::factory()->create();
        PackingList::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->get(route('packing-lists.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('PackingLists/Index')
                ->has('packingLists', 3)
            );
    }

    public function test_user_can_create_a_packing_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('packing-lists.store'), [
                'name' => 'Paris Trip',
                'destination' => 'Paris',
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-07',
                'occasions' => ['casual', 'formal'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('packing_lists', [
            'user_id' => $user->id,
            'name' => 'Paris Trip',
            'destination' => 'Paris',
        ]);
    }

    public function test_user_can_view_their_packing_list(): void
    {
        $user = User::factory()->create();
        $packingList = PackingList::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('packing-lists.show', $packingList))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('PackingLists/Show')
                ->where('packingList.id', $packingList->id)
                ->where('packingList.name', $packingList->name)
            );
    }

    public function test_user_cannot_view_other_users_packing_list(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $packingList = PackingList::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('packing-lists.show', $packingList))
            ->assertForbidden();
    }

    public function test_user_can_delete_their_packing_list(): void
    {
        $user = User::factory()->create();
        $packingList = PackingList::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('packing-lists.destroy', $packingList))
            ->assertRedirect();

        $this->assertDatabaseMissing('packing_lists', [
            'id' => $packingList->id,
        ]);
    }

    public function test_user_can_add_garment_to_packing_list(): void
    {
        $user = User::factory()->create();
        $packingList = PackingList::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('packing-lists.items.add', $packingList), [
                'garment_id' => $garment->id,
                'day_number' => 1,
                'occasion' => 'casual',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('packing_list_items', [
            'packing_list_id' => $packingList->id,
            'garment_id' => $garment->id,
            'day_number' => 1,
            'occasion' => 'casual',
        ]);
    }

    public function test_user_can_toggle_packed_status(): void
    {
        $user = User::factory()->create();
        $packingList = PackingList::factory()->for($user)->create();
        $item = PackingListItem::factory()->for($packingList)->create(['is_packed' => false]);

        $this->actingAs($user)
            ->patch(route('packing-lists.items.toggle', [$packingList, $item]))
            ->assertRedirect();

        $this->assertTrue($item->fresh()->is_packed);

        $this->actingAs($user)
            ->patch(route('packing-lists.items.toggle', [$packingList, $item]))
            ->assertRedirect();

        $this->assertFalse($item->fresh()->is_packed);
    }

    public function test_user_can_remove_item_from_packing_list(): void
    {
        $user = User::factory()->create();
        $packingList = PackingList::factory()->for($user)->create();
        $item = PackingListItem::factory()->for($packingList)->create();

        $this->actingAs($user)
            ->delete(route('packing-lists.items.remove', [$packingList, $item]))
            ->assertRedirect();

        $this->assertDatabaseMissing('packing_list_items', [
            'id' => $item->id,
        ]);
    }

    public function test_packing_list_show_passes_garments_for_add_dialog(): void
    {
        $user = User::factory()->create();
        $packingList = PackingList::factory()->for($user)->create();
        Garment::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->get(route('packing-lists.show', $packingList))
            ->assertInertia(fn ($page) => $page
                ->component('PackingLists/Show')
                ->has('garments', 3)
            );
    }

    public function test_creating_packing_list_without_name_fails_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('packing-lists.store'), [
                'destination' => 'Paris',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_creating_packing_list_with_empty_name_fails_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('packing-lists.store'), [
                'name' => '',
                'destination' => 'Paris',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_packing_list_index_includes_packed_items_count(): void
    {
        $user = User::factory()->create();
        $packingList = PackingList::factory()->for($user)->create();
        $garment1 = Garment::factory()->for($user)->create();
        $garment2 = Garment::factory()->for($user)->create();
        $garment3 = Garment::factory()->for($user)->create();

        PackingListItem::factory()->for($packingList)->create([
            'garment_id' => $garment1->id,
            'is_packed' => true,
        ]);
        PackingListItem::factory()->for($packingList)->create([
            'garment_id' => $garment2->id,
            'is_packed' => true,
        ]);
        PackingListItem::factory()->for($packingList)->create([
            'garment_id' => $garment3->id,
            'is_packed' => false,
        ]);

        $this->actingAs($user)
            ->get(route('packing-lists.index'))
            ->assertInertia(fn ($page) => $page
                ->has('packingLists', 1)
                ->where('packingLists.0.total_count', 3)
                ->where('packingLists.0.packed_count', 2)
            );
    }

    public function test_user_only_sees_own_packing_lists(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        PackingList::factory(2)->for($user)->create();
        PackingList::factory(3)->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('packing-lists.index'))
            ->assertInertia(fn ($page) => $page->has('packingLists', 2));
    }

    public function test_user_cannot_delete_other_users_packing_list(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $packingList = PackingList::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->delete(route('packing-lists.destroy', $packingList))
            ->assertForbidden();

        $this->assertDatabaseHas('packing_lists', ['id' => $packingList->id]);
    }

    public function test_end_date_must_be_after_start_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('packing-lists.store'), [
                'name' => 'Trip',
                'start_date' => '2026-03-10',
                'end_date' => '2026-03-05',
            ])
            ->assertSessionHasErrors('end_date');
    }
}
