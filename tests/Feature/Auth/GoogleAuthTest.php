<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_route_exists(): void
    {
        $this->get(route('auth.google'))
            ->assertRedirect();
    }

    public function test_google_callback_creates_new_user(): void
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
        $googleUser->shouldReceive('getName')->andReturn('New User');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($googleUser)->getMock());

        $this->get('/auth/google/callback')
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'google_id' => 'google-123',
            'name' => 'New User',
        ]);

        $this->assertAuthenticated();
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
        ]);

        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('google-456');
        $googleUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $googleUser->shouldReceive('getName')->andReturn('Existing User');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($googleUser)->getMock());

        $this->get('/auth/google/callback')
            ->assertRedirect(route('dashboard'));

        $existingUser->refresh();
        $this->assertEquals('google-456', $existingUser->google_id);
        $this->assertEquals('https://example.com/avatar.jpg', $existingUser->avatar);

        $this->assertAuthenticatedAs($existingUser);
        $this->assertEquals(1, User::count());
    }

    public function test_google_callback_links_existing_user_by_google_id(): void
    {
        $existingUser = User::factory()->create([
            'google_id' => 'google-789',
        ]);

        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getId')->andReturn('google-789');
        $googleUser->shouldReceive('getEmail')->andReturn($existingUser->email);
        $googleUser->shouldReceive('getName')->andReturn('User');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/new-avatar.jpg');

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($googleUser)->getMock());

        $this->get('/auth/google/callback')
            ->assertRedirect(route('dashboard'));

        $existingUser->refresh();
        $this->assertEquals('https://example.com/new-avatar.jpg', $existingUser->avatar);

        $this->assertAuthenticatedAs($existingUser);
        $this->assertEquals(1, User::count());
    }
}
