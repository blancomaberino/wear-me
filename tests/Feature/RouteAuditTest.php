<?php

namespace Tests\Feature;

use App\Models\Garment;
use App\Models\Lookbook;
use App\Models\OutfitSuggestion;
use App\Models\OutfitTemplate;
use App\Models\TryOnResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function dashboard_loads(): void
    {
        $this->actingAs($this->user)->get('/dashboard')->assertStatus(200);
    }

    /** @test */
    public function wardrobe_index_loads(): void
    {
        $this->actingAs($this->user)->get('/wardrobe')->assertStatus(200);
    }

    /** @test */
    public function tryon_index_loads(): void
    {
        $this->actingAs($this->user)->get('/tryon')->assertStatus(200);
    }

    /** @test */
    public function tryon_history_loads(): void
    {
        $this->actingAs($this->user)->get('/tryon/history')->assertStatus(200);
    }

    /** @test */
    public function model_images_index_loads(): void
    {
        $this->actingAs($this->user)->get('/model-images')->assertStatus(200);
    }

    /** @test */
    public function videos_index_loads(): void
    {
        $this->actingAs($this->user)->get('/videos')->assertStatus(200);
    }

    /** @test */
    public function videos_history_loads(): void
    {
        $this->actingAs($this->user)->get('/videos/history')->assertStatus(200);
    }

    /** @test */
    public function outfits_index_loads(): void
    {
        $this->actingAs($this->user)->get('/outfits')->assertStatus(200);
    }

    /** @test */
    public function outfits_saved_loads(): void
    {
        $this->actingAs($this->user)->get('/outfits/saved')->assertStatus(200);
    }

    /** @test */
    public function outfits_templates_loads(): void
    {
        $this->actingAs($this->user)->get('/outfits/templates')->assertStatus(200);
    }

    /** @test */
    public function my_outfits_index_loads(): void
    {
        $this->actingAs($this->user)->get('/my-outfits')->assertStatus(200);
    }

    /** @test */
    public function lookbooks_index_loads(): void
    {
        $this->actingAs($this->user)->get('/lookbooks')->assertStatus(200);
    }

    /** @test */
    public function packing_lists_index_loads(): void
    {
        $this->actingAs($this->user)->get('/packing-lists')->assertStatus(200);
    }

    /** @test */
    public function share_my_links_loads(): void
    {
        $this->actingAs($this->user)->get('/share/my-links')->assertStatus(200);
    }

    /** @test */
    public function profile_loads(): void
    {
        $this->actingAs($this->user)->get('/profile')->assertStatus(200);
    }

    /** @test */
    public function tryon_show_loads(): void
    {
        $result = TryOnResult::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user)->get('/tryon/' . $result->id)->assertStatus(200);
    }

    /** @test */
    public function tryon_status_loads(): void
    {
        $result = TryOnResult::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user)->get('/tryon/' . $result->id . '/status')->assertStatus(200);
    }

    /** @test */
    public function lookbook_show_loads(): void
    {
        $lookbook = Lookbook::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->actingAs($this->user)->get('/lookbooks/' . $lookbook->id)->assertStatus(200);
    }

    /** @test */
    public function videos_index_with_tryon_results_loads(): void
    {
        // Create a try-on result with garments (multi-garment)
        $result = TryOnResult::factory()->create([
            'user_id' => $this->user->id,
            'status' => \App\Enums\ProcessingStatus::Completed,
        ]);
        $garment = Garment::factory()->create(['user_id' => $this->user->id]);
        $result->garments()->attach($garment->id);

        $this->actingAs($this->user)->get('/videos')->assertStatus(200);
    }

    /** @test */
    public function videos_index_with_legacy_garment_loads(): void
    {
        // Create a try-on result with legacy single garment_id
        $garment = Garment::factory()->create(['user_id' => $this->user->id]);
        $result = TryOnResult::factory()->create([
            'user_id' => $this->user->id,
            'status' => \App\Enums\ProcessingStatus::Completed,
            'garment_id' => $garment->id,
        ]);

        $this->actingAs($this->user)->get('/videos')->assertStatus(200);
    }

    /** @test */
    public function videos_index_with_null_garment_loads(): void
    {
        // Try-on result where garment was deleted
        $result = TryOnResult::factory()->create([
            'user_id' => $this->user->id,
            'status' => \App\Enums\ProcessingStatus::Completed,
            'garment_id' => null,
        ]);

        $this->actingAs($this->user)->get('/videos')->assertStatus(200);
    }

    /** @test */
    public function outfits_suggestions_with_data_loads(): void
    {
        OutfitSuggestion::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user)->get('/outfits')->assertStatus(200);
    }

    /** @test */
    public function outfits_templates_with_seeded_data_loads(): void
    {
        $this->seed(\Database\Seeders\OutfitTemplateSeeder::class);
        $this->actingAs($this->user)->get('/outfits/templates')->assertStatus(200);
    }

    /** @test */
    public function all_unauthenticated_routes_redirect_to_login(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/wardrobe',
            '/tryon',
            '/tryon/history',
            '/model-images',
            '/videos',
            '/videos/history',
            '/outfits',
            '/outfits/saved',
            '/outfits/templates',
            '/my-outfits',
            '/lookbooks',
            '/packing-lists',
            '/share/my-links',
            '/profile',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $this->assertTrue(
                $response->isRedirect(),
                "Route {$route} should redirect unauthenticated users but got status {$response->getStatusCode()}"
            );
        }
    }

    /** @test */
    public function public_share_route_with_invalid_token_returns_404(): void
    {
        $this->get('/s/nonexistent-token')->assertStatus(404);
    }
}
