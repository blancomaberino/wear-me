<?php

namespace Tests\Feature;

use App\Models\Garment;
use App\Models\Outfit;
use App\Models\OutfitTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutfitTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_outfits_pages(): void
    {
        $this->get(route('outfits.templates'))->assertRedirect(route('login'));
        $this->get(route('my-outfits.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_outfit_templates(): void
    {
        $user = User::factory()->create();
        OutfitTemplate::factory(5)->create();

        $this->actingAs($user)
            ->get(route('outfits.templates'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/Templates')
                ->has('templates', 5)
            );
    }

    public function test_user_can_view_my_outfits_index(): void
    {
        $user = User::factory()->create();
        Outfit::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->get(route('my-outfits.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/MyOutfits')
                ->has('outfits', 3)
            );
    }

    public function test_user_can_create_an_outfit_with_garments(): void
    {
        $user = User::factory()->create();
        $template = OutfitTemplate::factory()->create();
        $garment1 = Garment::factory()->for($user)->create(['category' => 'upper']);
        $garment2 = Garment::factory()->for($user)->create(['category' => 'lower']);

        $this->actingAs($user)
            ->post(route('my-outfits.store'), [
                'name' => 'Casual Friday',
                'outfit_template_id' => $template->id,
                'occasion' => 'casual',
                'garments' => [
                    ['garment_id' => $garment1->id, 'slot_label' => 'Top', 'sort_order' => 0],
                    ['garment_id' => $garment2->id, 'slot_label' => 'Bottom', 'sort_order' => 1],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('outfits', [
            'user_id' => $user->id,
            'name' => 'Casual Friday',
            'outfit_template_id' => $template->id,
        ]);

        $outfit = Outfit::where('user_id', $user->id)->first();
        $this->assertCount(2, $outfit->garments);
    }

    public function test_user_can_delete_their_outfit(): void
    {
        $user = User::factory()->create();
        $outfit = Outfit::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('my-outfits.destroy', $outfit))
            ->assertRedirect();

        $this->assertDatabaseMissing('outfits', [
            'id' => $outfit->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_outfit(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $outfit = Outfit::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->delete(route('my-outfits.destroy', $outfit))
            ->assertForbidden();

        $this->assertDatabaseHas('outfits', [
            'id' => $outfit->id,
        ]);
    }

    public function test_creating_outfit_stores_pivot_records_with_slot_labels(): void
    {
        $user = User::factory()->create();
        $template = OutfitTemplate::factory()->create();
        $garment1 = Garment::factory()->for($user)->create(['category' => 'upper']);
        $garment2 = Garment::factory()->for($user)->create(['category' => 'lower']);

        $this->actingAs($user)
            ->post(route('my-outfits.store'), [
                'name' => 'My Look',
                'outfit_template_id' => $template->id,
                'occasion' => 'casual',
                'garments' => [
                    ['garment_id' => $garment1->id, 'slot_label' => 'Top', 'sort_order' => 0],
                    ['garment_id' => $garment2->id, 'slot_label' => 'Bottom', 'sort_order' => 1],
                ],
            ])
            ->assertRedirect();

        $outfit = Outfit::where('user_id', $user->id)->first();
        $this->assertNotNull($outfit);
        $this->assertCount(2, $outfit->garments);

        // Verify pivot data
        $pivots = $outfit->garments->pluck('pivot.slot_label')->toArray();
        $this->assertContains('Top', $pivots);
        $this->assertContains('Bottom', $pivots);
    }

    public function test_templates_page_passes_correct_data(): void
    {
        $user = User::factory()->create();
        $template = OutfitTemplate::factory()->create([
            'slots' => [
                ['label' => 'Top', 'category' => 'upper'],
                ['label' => 'Bottom', 'category' => 'lower'],
                ['label' => 'Shoes', 'category' => 'footwear'],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('outfits.templates'))
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/Templates')
                ->has('templates', 1)
                ->has('templates.0.slots', 3)
            );
    }

    public function test_creating_outfit_without_name_fails_validation(): void
    {
        $user = User::factory()->create();
        $garment = Garment::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('my-outfits.store'), [
                'garments' => [
                    ['garment_id' => $garment->id, 'slot_label' => 'Top', 'sort_order' => 0],
                ],
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_creating_outfit_without_garments_fails_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('my-outfits.store'), [
                'name' => 'Empty Outfit',
                'garments' => [],
            ])
            ->assertSessionHasErrors('garments');
    }

    public function test_my_outfits_index_only_shows_own_outfits(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Outfit::factory(2)->for($user)->create();
        Outfit::factory(3)->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('my-outfits.index'))
            ->assertInertia(fn ($page) => $page->has('outfits', 2));
    }

    public function test_user_can_view_single_outfit(): void
    {
        $user = User::factory()->create();
        $outfit = Outfit::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();
        $outfit->garments()->attach($garment->id, [
            'slot_label' => 'Top',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('my-outfits.show', $outfit))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/ShowOutfit')
                ->where('outfit.id', $outfit->id)
                ->has('outfit.garments', 1)
                ->has('garments')
            );
    }

    public function test_user_cannot_view_other_users_outfit(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $outfit = Outfit::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('my-outfits.show', $outfit))
            ->assertForbidden();
    }

    public function test_my_outfits_index_includes_template_and_garments(): void
    {
        $user = User::factory()->create();
        $template = OutfitTemplate::factory()->create();
        $garment = Garment::factory()->for($user)->create();

        $outfit = Outfit::factory()->for($user)->create([
            'outfit_template_id' => $template->id,
        ]);
        $outfit->garments()->attach($garment->id, [
            'slot_label' => 'Top',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('my-outfits.index'))
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/MyOutfits')
                ->has('outfits', 1)
                ->has('outfits.0.template')
                ->has('outfits.0.garments', 1)
            );
    }
}
