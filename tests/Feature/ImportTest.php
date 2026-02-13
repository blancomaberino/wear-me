<?php

namespace Tests\Feature;

use App\Models\Garment;
use App\Models\User;
use App\Services\Scraper\ScraperService;
use App\Contracts\ScrapedProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_import_preview(): void
    {
        $this->postJson(route('import.preview'), ['url' => 'https://www.amazon.com/test'])
            ->assertUnauthorized();
    }

    public function test_preview_requires_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('import.preview'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('url');
    }

    public function test_preview_requires_valid_url(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('import.preview'), ['url' => 'not-a-url'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('url');
    }

    public function test_preview_returns_scraped_product(): void
    {
        $user = User::factory()->create();

        $mockProduct = new ScrapedProduct(
            name: 'Test T-Shirt',
            brand: 'TestBrand',
            price: null,
            currency: null,
            imageUrls: ['https://example.com/image.jpg'],
            categoryHint: null,
            sizeOptions: [],
            material: 'Cotton',
            description: 'A test product',
            sourceUrl: 'https://www.amazon.com/test',
            sourceProvider: 'amazon',
        );

        $this->mock(ScraperService::class, function ($mock) use ($mockProduct) {
            $mock->shouldReceive('scrape')
                ->once()
                ->andReturn($mockProduct);
        });

        $this->actingAs($user)
            ->postJson(route('import.preview'), ['url' => 'https://www.amazon.com/test'])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'product' => [
                    'name' => 'Test T-Shirt',
                    'brand' => 'TestBrand',
                    'image_url' => 'https://example.com/image.jpg',
                    'source_provider' => 'amazon',
                ],
            ]);
    }

    public function test_preview_returns_error_on_scrape_failure(): void
    {
        $user = User::factory()->create();

        $this->mock(ScraperService::class, function ($mock) {
            $mock->shouldReceive('scrape')
                ->once()
                ->andThrow(new \RuntimeException('Connection timeout'));
        });

        $this->actingAs($user)
            ->postJson(route('import.preview'), ['url' => 'https://www.amazon.com/test'])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_confirm_requires_authentication(): void
    {
        $this->post(route('import.confirm'), [
            'source_url' => 'https://www.amazon.com/test',
            'image_url' => 'https://example.com/image.jpg',
            'category' => 'upper',
        ])->assertRedirect(route('login'));
    }

    public function test_confirm_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('import.confirm'), [])
            ->assertSessionHasErrors(['source_url', 'image_url', 'category']);
    }

    public function test_confirm_validates_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('import.confirm'), [
                'source_url' => 'https://www.amazon.com/test',
                'image_url' => 'https://example.com/image.jpg',
                'category' => 'invalid_category',
            ])
            ->assertSessionHasErrors('category');
    }

    public function test_confirm_enforces_garment_limit(): void
    {
        $user = User::factory()->create();
        Garment::factory(User::MAX_GARMENTS)->for($user)->create();

        $this->actingAs($user)
            ->post(route('import.confirm'), [
                'source_url' => 'https://www.amazon.com/test',
                'image_url' => 'https://example.com/image.jpg',
                'category' => 'upper',
            ])
            ->assertSessionHasErrors('url');
    }

    public function test_confirm_downloads_image_and_creates_garment(): void
    {
        $user = User::factory()->create();

        // Create a 1x1 pixel JPEG to return from the fake HTTP
        $pixel = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////2wBDAf//////////////////////////////////////////////////////////////////////////////////////wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AKwA//9k=');

        Http::fake([
            'example.com/*' => Http::response($pixel, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        Storage::fake('public');
        Storage::fake('local');

        $this->actingAs($user)
            ->post(route('import.confirm'), [
                'source_url' => 'https://www.amazon.com/test',
                'image_url' => 'https://example.com/image.jpg',
                'name' => 'Test T-Shirt',
                'category' => 'upper',
                'brand' => 'TestBrand',
                'material' => 'Cotton',
                'source_provider' => 'amazon',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('garments', [
            'user_id' => $user->id,
            'category' => 'upper',
            'name' => 'Test T-Shirt',
            'brand' => 'TestBrand',
            'source_url' => 'https://www.amazon.com/test',
        ]);
    }
}
