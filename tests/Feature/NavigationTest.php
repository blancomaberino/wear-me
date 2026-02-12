<?php

namespace Tests\Feature;

use App\Models\Garment;
use App\Models\Lookbook;
use App\Models\ModelImage;
use App\Models\OutfitSuggestion;
use App\Models\TryOnResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // Inertia component name verification for ALL pages
    // -------------------------------------------------------

    public function test_dashboard_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_wardrobe_index_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('wardrobe.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Wardrobe/Index'));
    }

    public function test_tryon_index_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('tryon.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('TryOn/Index'));
    }

    public function test_tryon_result_renders_correct_component(): void
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
            ->assertInertia(fn ($page) => $page->component('TryOn/Result'));
    }

    public function test_tryon_history_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('tryon.history'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('TryOn/History'));
    }

    public function test_outfit_suggestions_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('outfits.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Outfits/Suggestions'));
    }

    public function test_outfit_saved_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('outfits.saved'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Outfits/Saved'));
    }

    public function test_lookbooks_index_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('lookbooks.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Lookbooks/Index'));
    }

    public function test_packing_lists_index_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('packing-lists.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('PackingLists/Index'));
    }

    public function test_outfit_templates_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('outfits.templates'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Outfits/Templates'));
    }

    public function test_my_outfits_index_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('my-outfits.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Outfits/MyOutfits'));
    }

    public function test_model_images_index_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('model-images.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('ModelImages/Index'));
    }

    public function test_videos_index_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('videos.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Videos/Index'));
    }

    public function test_videos_history_renders_correct_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('videos.history'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Videos/History'));
    }

    // -------------------------------------------------------
    // Guest redirect tests for ALL protected routes
    // -------------------------------------------------------

    public function test_guests_are_redirected_to_login_for_protected_pages(): void
    {
        $protectedRoutes = [
            'dashboard',
            'wardrobe.index',
            'tryon.index',
            'tryon.history',
            'outfits.index',
            'outfits.saved',
            'outfits.templates',
            'lookbooks.index',
            'packing-lists.index',
            'my-outfits.index',
            'model-images.index',
            'videos.index',
            'videos.history',
        ];

        foreach ($protectedRoutes as $routeName) {
            $this->get(route($routeName))
                ->assertRedirect(route('login'));
        }
    }

    // -------------------------------------------------------
    // Shared Inertia props verification
    // -------------------------------------------------------

    public function test_all_pages_include_auth_shared_prop(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->has('auth.user')
                ->where('auth.user.id', $user->id)
                ->where('auth.user.name', $user->name)
                ->where('auth.user.email', $user->email)
            );
    }

    public function test_auth_prop_includes_locale_field(): void
    {
        $user = User::factory()->create(['locale' => 'es']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('auth.user.locale', 'es')
            );
    }

    public function test_all_pages_include_locale_shared_prop(): void
    {
        $user = User::factory()->create(['locale' => 'es']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'es')
                ->where('availableLocales', ['en', 'es'])
            );
    }

    public function test_auth_user_is_null_for_guests_on_public_pages(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('auth.user', null)
            );
    }

    // -------------------------------------------------------
    // TryOn result page includes lookbooks prop
    // -------------------------------------------------------

    public function test_tryon_result_includes_lookbooks_prop(): void
    {
        $user = User::factory()->create();
        $modelImage = ModelImage::factory()->for($user)->create();
        $garment = Garment::factory()->for($user)->create();
        Lookbook::factory(2)->for($user)->create();

        $result = TryOnResult::factory()->completed()->for($user)->create([
            'model_image_id' => $modelImage->id,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($user)
            ->get(route('tryon.show', $result))
            ->assertInertia(fn ($page) => $page
                ->component('TryOn/Result')
                ->has('lookbooks', 2)
            );
    }

    // -------------------------------------------------------
    // Outfit suggestions page includes lookbooks prop
    // -------------------------------------------------------

    public function test_suggestions_page_includes_lookbooks_prop(): void
    {
        $user = User::factory()->create();
        Lookbook::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->get(route('outfits.index'))
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/Suggestions')
                ->has('lookbooks', 3)
            );
    }

    public function test_saved_suggestions_page_includes_lookbooks_prop(): void
    {
        $user = User::factory()->create();
        Lookbook::factory(2)->for($user)->create();

        $this->actingAs($user)
            ->get(route('outfits.saved'))
            ->assertInertia(fn ($page) => $page
                ->component('Outfits/Saved')
                ->has('lookbooks', 2)
            );
    }

    // -------------------------------------------------------
    // Welcome page is accessible without auth
    // -------------------------------------------------------

    public function test_welcome_page_is_accessible(): void
    {
        $this->get('/')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Welcome'));
    }
}
