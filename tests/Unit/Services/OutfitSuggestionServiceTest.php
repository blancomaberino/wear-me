<?php

namespace Tests\Unit\Services;

use App\Models\Garment;
use App\Models\User;
use App\Services\OutfitSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OutfitSuggestionServiceTest extends TestCase
{
    use RefreshDatabase;

    private OutfitSuggestionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OutfitSuggestionService();
    }

    public function test_throws_exception_with_less_than_two_garments(): void
    {
        $user = User::factory()->create();
        Garment::factory()->for($user)->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('at least 2 garments');

        $this->service->generateSuggestions($user);
    }

    public function test_throws_exception_with_zero_garments(): void
    {
        $user = User::factory()->create();

        $this->expectException(\RuntimeException::class);

        $this->service->generateSuggestions($user);
    }

    public function test_returns_parsed_suggestions_from_api(): void
    {
        $user = User::factory()->create();
        Garment::factory(3)->for($user)->create();

        $suggestions = [
            ['garment_ids' => [1, 2], 'suggestion' => 'Great combo'],
            ['garment_ids' => [2, 3], 'suggestion' => 'Nice pairing'],
        ];

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => json_encode($suggestions)],
                ],
            ]),
        ]);

        $result = $this->service->generateSuggestions($user, 'casual');

        $this->assertCount(2, $result);
        $this->assertEquals([1, 2], $result[0]['garment_ids']);
        $this->assertEquals('Great combo', $result[0]['suggestion']);

        Http::assertSentCount(1);
    }

    public function test_handles_json_embedded_in_text(): void
    {
        $user = User::factory()->create();
        Garment::factory(2)->for($user)->create();

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => "Here are some suggestions:\n\n[{\"garment_ids\":[1,2],\"suggestion\":\"Test\"}]\n\nHope that helps!"],
                ],
            ]),
        ]);

        $result = $this->service->generateSuggestions($user);

        $this->assertCount(1, $result);
        $this->assertEquals([1, 2], $result[0]['garment_ids']);
    }

    public function test_throws_exception_on_api_failure(): void
    {
        $user = User::factory()->create();
        Garment::factory(2)->for($user)->create();

        Http::fake([
            'api.anthropic.com/*' => Http::response('Server Error', 500),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get outfit suggestions');

        $this->service->generateSuggestions($user);
    }

    public function test_returns_empty_array_when_no_json_in_response(): void
    {
        $user = User::factory()->create();
        Garment::factory(2)->for($user)->create();

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => 'No JSON here, just text.'],
                ],
            ]),
        ]);

        $result = $this->service->generateSuggestions($user);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_sends_correct_occasion_to_api(): void
    {
        $user = User::factory()->create();
        Garment::factory(2)->for($user)->create();

        Http::fake([
            'api.anthropic.com/*' => Http::response([
                'content' => [
                    ['text' => '[]'],
                ],
            ]),
        ]);

        $this->service->generateSuggestions($user, 'evening', 5);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $prompt = $body['messages'][0]['content'];
            return str_contains($prompt, "'evening'") && str_contains($prompt, '5 complete outfit');
        });
    }
}
