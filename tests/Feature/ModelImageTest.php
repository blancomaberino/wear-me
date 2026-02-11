<?php

namespace Tests\Feature;

use App\Models\ModelImage;
use App\Models\User;
use App\Services\ImageProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModelImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_guests_cannot_access_model_images(): void
    {
        $this->get(route('model-images.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_model_images_index(): void
    {
        $user = User::factory()->create();
        ModelImage::factory(3)->for($user)->create();

        $this->actingAs($user)
            ->get(route('model-images.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('ModelImages/Index')
                ->has('images', 3)
            );
    }

    public function test_user_only_sees_own_images(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ModelImage::factory(2)->for($user)->create();
        ModelImage::factory(3)->for($otherUser)->create();

        $this->actingAs($user)
            ->get(route('model-images.index'))
            ->assertInertia(fn ($page) => $page->has('images', 2));
    }

    public function test_user_can_upload_model_image(): void
    {
        $user = User::factory()->create();

        $this->mock(ImageProcessingService::class, function ($mock) {
            $mock->shouldReceive('processAndStore')
                ->once()
                ->andReturn([
                    'path' => 'model-images/test.jpg',
                    'thumbnail_path' => 'model-images/thumbnails/thumb_test.jpg',
                    'width' => 800,
                    'height' => 1200,
                    'size_bytes' => 500000,
                    'original_filename' => 'photo.jpg',
                ]);
        });

        $this->actingAs($user)
            ->post(route('model-images.store'), [
                'image' => UploadedFile::fake()->image('photo.jpg', 800, 1200),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('model_images', [
            'user_id' => $user->id,
            'path' => 'model-images/test.jpg',
            'original_filename' => 'photo.jpg',
        ]);
    }

    public function test_upload_requires_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('model-images.store'), [])
            ->assertSessionHasErrors('image');
    }

    public function test_upload_rejects_non_image_file(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('model-images.store'), [
                'image' => UploadedFile::fake()->create('document.pdf', 1000),
            ])
            ->assertSessionHasErrors('image');
    }

    public function test_upload_rejects_oversized_file(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('model-images.store'), [
                'image' => UploadedFile::fake()->image('huge.jpg')->size(11000),
            ])
            ->assertSessionHasErrors('image');
    }

    public function test_user_can_set_primary_image(): void
    {
        $user = User::factory()->create();
        $image1 = ModelImage::factory()->primary()->for($user)->create();
        $image2 = ModelImage::factory()->for($user)->create();

        $this->actingAs($user)
            ->patch(route('model-images.primary', $image2))
            ->assertRedirect();

        $this->assertFalse($image1->fresh()->is_primary);
        $this->assertTrue($image2->fresh()->is_primary);
    }

    public function test_user_cannot_set_primary_on_other_users_image(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $image = ModelImage::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->patch(route('model-images.primary', $image))
            ->assertForbidden();
    }

    public function test_user_can_delete_model_image(): void
    {
        $user = User::factory()->create();
        Storage::disk('public')->put('model-images/test.jpg', 'content');
        Storage::disk('public')->put('model-images/thumbnails/thumb.jpg', 'content');

        $image = ModelImage::factory()->for($user)->create([
            'path' => 'model-images/test.jpg',
            'thumbnail_path' => 'model-images/thumbnails/thumb.jpg',
        ]);

        $this->actingAs($user)
            ->delete(route('model-images.destroy', $image))
            ->assertRedirect();

        $this->assertSoftDeleted('model_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing('model-images/test.jpg');
        Storage::disk('public')->assertMissing('model-images/thumbnails/thumb.jpg');
    }

    public function test_user_cannot_delete_other_users_image(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $image = ModelImage::factory()->for($otherUser)->create();

        $this->actingAs($user)
            ->delete(route('model-images.destroy', $image))
            ->assertForbidden();
    }
}
