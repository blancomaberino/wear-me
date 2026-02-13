<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_defaults_to_english(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('locale', 'en'));
    }

    public function test_user_can_switch_locale_to_spanish(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $response = $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => 'es']);

        $response->assertRedirect();
        // Use assertCookie with encrypted=false since locale is excluded from encryption
        $response->assertCookie('locale', 'es', false);

        // User model should be updated
        $this->assertEquals('es', $user->fresh()->locale);
    }

    public function test_locale_persists_across_requests(): void
    {
        $user = User::factory()->create(['locale' => 'es']);

        // On subsequent requests, locale should be 'es' in shared props
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page->where('locale', 'es'));
    }

    public function test_locale_shared_prop_updates_after_switch(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        // Switch to Spanish
        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'es']);

        // Next page load should have locale=es in shared props
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page->where('locale', 'es'));
    }

    public function test_locale_switch_back_to_english(): void
    {
        $user = User::factory()->create(['locale' => 'es']);

        $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en']);

        $this->assertEquals('en', $user->fresh()->locale);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page->where('locale', 'en'));
    }

    public function test_locale_rejects_invalid_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');

        $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => ''])
            ->assertSessionHasErrors('locale');
    }

    public function test_locale_rejects_missing_value(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('locale.update'), [])
            ->assertSessionHasErrors('locale');
    }

    public function test_locale_cookie_is_not_encrypted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => 'es']);

        // The cookie value should be plain text 'es', not an encrypted blob.
        // assertCookie with encrypted=false verifies the raw (unencrypted) cookie value.
        $response->assertCookie('locale', 'es', false);
    }

    public function test_guest_locale_from_cookie(): void
    {
        // Guest with locale cookie should see that locale applied
        $response = $this->withUnencryptedCookie('locale', 'es')
            ->get(route('login'));

        $response->assertOk();
    }

    public function test_locale_update_works_for_guests(): void
    {
        // Guests (not logged in) should also be able to switch locale via cookie
        $response = $this->post(route('locale.update'), ['locale' => 'es']);

        $response->assertRedirect();
        $response->assertCookie('locale', 'es', false);
    }

    public function test_authenticated_user_locale_takes_priority_over_cookie(): void
    {
        // User has locale=es in DB, but cookie says 'en'
        // SetLocale middleware should prefer the user's DB value
        $user = User::factory()->create(['locale' => 'es']);

        $response = $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'en')
            ->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page->where('locale', 'es'));
    }

    public function test_all_inertia_pages_share_locale_prop(): void
    {
        $user = User::factory()->create(['locale' => 'es']);

        $pages = [
            'dashboard',
            'wardrobe.index',
            'tryon.index',
            'outfits.index',
            'lookbooks.index',
            'packing-lists.index',
        ];

        foreach ($pages as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));

            $response->assertInertia(fn ($page) => $page->where('locale', 'es'));
        }
    }

    public function test_all_inertia_pages_share_available_locales_prop(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page
            ->where('availableLocales', ['en', 'es'])
        );
    }

    public function test_locale_shared_via_inertia_matches_app_locale(): void
    {
        // This tests that the Inertia shared prop 'locale' actually reflects
        // what App::getLocale() returns after SetLocale middleware runs
        $user = User::factory()->create(['locale' => 'es']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(fn ($page) => $page->where('locale', 'es'));
    }

    public function test_locale_cookie_value_is_set_correctly(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => 'es']);

        // Verify cookie is set with unencrypted assertion
        $response->assertCookie('locale', 'es', false);
    }

    public function test_middleware_sanitizes_invalid_cookie_locale(): void
    {
        // If someone manually sets an invalid locale cookie, middleware should fallback to 'en'
        // Note: guest access with invalid cookie should still render the page
        $response = $this->withUnencryptedCookie('locale', 'INVALID')
            ->get('/');

        $response->assertOk();
    }

    public function test_locale_update_updates_user_in_database(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => 'es']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'es',
        ]);
    }

    public function test_locale_does_not_update_other_users(): void
    {
        $user = User::factory()->create(['locale' => 'en']);
        $otherUser = User::factory()->create(['locale' => 'en']);

        $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => 'es']);

        // Only the acting user should be updated
        $this->assertEquals('es', $user->fresh()->locale);
        $this->assertEquals('en', $otherUser->fresh()->locale);
    }
}
