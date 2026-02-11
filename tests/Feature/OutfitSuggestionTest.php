<?php

namespace Tests\Feature;

use App\Jobs\GenerateOutfitSuggestion;
use App\Models\Garment;
use App\Models\OutfitSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OutfitSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_outfit_suggestions(): void
    {
        $this->get(route('outfits.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_suggestions_index(): void
    {
        $user = User::factory()->create();
        OutfitSuggestion::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->get(route('outfits.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/Suggestions')
                ->has('suggestions.data', 3)
                ->has('garmentCount')
            );
    }

    public function test_user_can_generate_suggestions(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('outfits.generate'), [
                'occasion' => 'casual',
            ])
            ->assertRedirect();

        Queue::assertPushed(GenerateOutfitSuggestion::class, function ($job) use ($user) {
            return true;
        });
    }

    public function test_generate_requires_valid_occasion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('outfits.generate'), [])
            ->assertSessionHasErrors('occasion');
    }

    public function test_generate_rejects_invalid_occasion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('outfits.generate'), [
                'occasion' => 'invalid_occasion',
            ])
            ->assertSessionHasErrors('occasion');
    }

    public function test_valid_occasions_accepted(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $validOccasions = ['casual', 'work', 'evening', 'sport', 'date'];

        foreach ($validOccasions as $occasion) {
            $this->actingAs($user)
                ->post(route('outfits.generate'), [
                    'occasion' => $occasion,
                ])
                ->assertRedirect()
                ->assertSessionHasNoErrors();
        }
    }

    public function test_user_can_toggle_saved(): void
    {
        $user = User::factory()->create();
        $suggestion = OutfitSuggestion::factory()->for($user)->create([
            'is_saved' => false,
        ]);

        $this->actingAs($user)
            ->patch(route('outfits.save', $suggestion))
            ->assertRedirect();

        $this->assertTrue($suggestion->fresh()->is_saved);

        $this->actingAs($user)
            ->patch(route('outfits.save', $suggestion))
            ->assertRedirect();

        $this->assertFalse($suggestion->fresh()->is_saved);
    }

    public function test_user_cannot_toggle_saved_on_other_users_suggestion(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $suggestion = OutfitSuggestion::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->patch(route('outfits.save', $suggestion))
            ->assertForbidden();
    }

    public function test_user_can_view_saved_suggestions(): void
    {
        $user = User::factory()->create();

        OutfitSuggestion::factory(3)->saved()->for($user)->create();
        OutfitSuggestion::factory(2)->for($user)->create(); // not saved

        $this->actingAs($user)
            ->get(route('outfits.saved'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/Saved')
                ->has('suggestions', 3)
            );
    }

    public function test_user_only_sees_own_suggestions(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        OutfitSuggestion::factory(2)->for($user)->create();
        OutfitSuggestion::factory(3)->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('outfits.index'))
            ->assertInertia(fn ($page) => $page
                ->has('suggestions.data', 2)
            );
    }

    public function test_suggestions_include_garment_count(): void
    {
        $user = User::factory()->create();
        Garment::factory(7)->for($user)->create();

        $this->actingAs($user)
            ->get(route('outfits.index'))
            ->assertInertia(fn ($page) => $page
                ->where('garmentCount', 7)
            );
    }
}
