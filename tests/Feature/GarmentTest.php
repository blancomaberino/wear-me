<?php

namespace Tests\Feature;

use App\Enums\GarmentCategory;
use App\Models\Garment;
use App\Models\User;
use App\Services\ImageProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GarmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guests_cannot_access_wardrobe(): void
    {
        $this->get(route('wardrobe.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_wardrobe(): void
    {
        $user = User::factory()->create();
        Garment::factory(5)->for($user)->create();

        $this->actingAs($user)
            ->get(route('wardrobe.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Wardrobe/Index')
                ->has('garments', 5)
                ->has('categories')
                ->where('currentCategory', 'all')
            );
    }

    public function test_user_can_filter_wardrobe_by_category(): void
    {
        $user = User::factory()->create();
        Garment::factory(3)->upper()->for($user)->create();
        Garment::factory(2)->lower()->for($user)->create();

        $this->actingAs($user)
            ->get(route('wardrobe.index', ['category' => 'upper']))
            ->assertInertia(fn ($page) => $page
                ->has('garments', 3)
                ->where('currentCategory', 'upper')
            );
    }

    public function test_user_only_sees_own_garments(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Garment::factory(2)->for($user)->create();
        Garment::factory(3)->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('wardrobe.index'))
            ->assertInertia(fn ($page) => $page->has('garments', 2));
    }

    public function test_user_can_upload_garment(): void
    {
        $user = User::factory()->create();

        $this->mock(ImageProcessingService::class, function ($mock) {
            $mock->shouldReceive('processAndStore')
                ->once()
                ->andReturn([
                    'path' => 'garments/test.jpg',
                    'thumbnail_path' => 'garments/thumbnails/thumb_test.jpg',
                    'width' => 600,
                    'height' => 800,
                    'size_bytes' => 300000,
                    'original_filename' => 'shirt.jpg',
                ]);
        });

        $this->actingAs($user)
            ->post(route('wardrobe.store'), [
                'image' => UploadedFile::fake()->image('shirt.jpg'),
                'category' => 'upper',
                'name' => 'Blue Shirt',
                'description' => 'A nice blue shirt',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('garments', [
            'user_id' => $user->id,
            'name' => 'Blue Shirt',
            'category' => 'upper',
            'description' => 'A nice blue shirt',
        ]);
    }

    public function test_upload_requires_image_and_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wardrobe.store'), [])
            ->assertSessionHasErrors(['image', 'category']);
    }

    public function test_upload_rejects_invalid_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wardrobe.store'), [
                'image' => UploadedFile::fake()->image('shirt.jpg'),
                'category' => 'invalid',
            ])
            ->assertSessionHasErrors('category');
    }

    public function test_user_can_update_garment(): void
    {
        $user = User::factory()->create();
        $garment = Garment::factory()->upper()->for($user)->create([
            'name' => 'Old Name',
        ]);

        $this->actingAs($user)
            ->patch(route('wardrobe.update', $garment), [
                'name' => 'New Name',
                'description' => 'Updated description',
                'category' => 'lower',
            ])
            ->assertRedirect();

        $garment->refresh();
        $this->assertEquals('New Name', $garment->name);
        $this->assertEquals('Updated description', $garment->description);
        $this->assertEquals(GarmentCategory::Lower, $garment->category);
    }

    public function test_user_cannot_update_other_users_garment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $garment = Garment::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->patch(route('wardrobe.update', $garment), ['name' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_user_can_delete_garment(): void
    {
        $user = User::factory()->create();
        Storage::disk('public')->put('garments/test.jpg', 'content');
        Storage::disk('public')->put('garments/thumbnails/thumb.jpg', 'content');

        $garment = Garment::factory()->for($user)->create([
            'path' => 'garments/test.jpg',
            'thumbnail_path' => 'garments/thumbnails/thumb.jpg',
        ]);

        $this->actingAs($user)
            ->delete(route('wardrobe.destroy', $garment))
            ->assertRedirect();

        $this->assertSoftDeleted('garments', ['id' => $garment->id]);
        Storage::disk('public')->assertMissing('garments/test.jpg');
        Storage::disk('public')->assertMissing('garments/thumbnails/thumb.jpg');
    }

    public function test_user_cannot_delete_other_users_garment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $garment = Garment::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->delete(route('wardrobe.destroy', $garment))
            ->assertForbidden();
    }

    public function test_name_and_description_are_optional(): void
    {
        $user = User::factory()->create();

        $this->mock(ImageProcessingService::class, function ($mock) {
            $mock->shouldReceive('processAndStore')
                ->once()
                ->andReturn([
                    'path' => 'garments/test.jpg',
                    'thumbnail_path' => 'garments/thumbnails/thumb_test.jpg',
                    'width' => 600,
                    'height' => 800,
                    'size_bytes' => 300000,
                    'original_filename' => 'shirt.jpg',
                ]);
        });

        $this->actingAs($user)
            ->post(route('wardrobe.store'), [
                'image' => UploadedFile::fake()->image('shirt.jpg'),
                'category' => 'dress',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('garments', [
            'user_id' => $user->id,
            'category' => 'dress',
        ]);
    }

    public function test_user_can_upload_garment_with_clothing_type(): void
    {
        $user = User::factory()->create();

        $this->mock(ImageProcessingService::class, function ($mock) {
            $mock->shouldReceive('processAndStore')
                ->once()
                ->andReturn([
                    'path' => 'garments/test.jpg',
                    'thumbnail_path' => 'garments/thumbnails/thumb_test.jpg',
                    'width' => 600,
                    'height' => 800,
                    'size_bytes' => 300000,
                    'original_filename' => 'shirt.jpg',
                ]);
        });

        $this->actingAs($user)
            ->post(route('wardrobe.store'), [
                'image' => UploadedFile::fake()->image('shirt.jpg'),
                'category' => 'upper',
                'clothing_type' => 'sweater',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('garments', [
            'user_id' => $user->id,
            'category' => 'upper',
            'clothing_type' => 'sweater',
        ]);
    }

    public function test_clothing_type_is_optional(): void
    {
        $user = User::factory()->create();

        $this->mock(ImageProcessingService::class, function ($mock) {
            $mock->shouldReceive('processAndStore')
                ->once()
                ->andReturn([
                    'path' => 'garments/test.jpg',
                    'thumbnail_path' => 'garments/thumbnails/thumb_test.jpg',
                    'width' => 600,
                    'height' => 800,
                    'size_bytes' => 300000,
                    'original_filename' => 'shirt.jpg',
                ]);
        });

        $this->actingAs($user)
            ->post(route('wardrobe.store'), [
                'image' => UploadedFile::fake()->image('shirt.jpg'),
                'category' => 'upper',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $garment = $user->garments()->first();
        $this->assertNull($garment->clothing_type);
    }

    public function test_clothing_type_max_length_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wardrobe.store'), [
                'image' => UploadedFile::fake()->image('shirt.jpg'),
                'category' => 'upper',
                'clothing_type' => str_repeat('a', 51),
            ])
            ->assertSessionHasErrors('clothing_type');
    }

    public function test_garment_resource_includes_clothing_type(): void
    {
        $user = User::factory()->create();
        Garment::factory()->for($user)->create([
            'clothing_type' => 'jacket',
        ]);

        $this->actingAs($user)
            ->get(route('wardrobe.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('garments', 1)
                ->where('garments.0.clothing_type', 'jacket')
            );
    }
}
