<?php

namespace App\Services;

use App\Models\Garment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutfitSuggestionService
{
    public function generateSuggestions(User $user, string $occasion = 'casual', int $count = 3): array
    {
        $garments = $user->garments()->get();

        if ($garments->count() < 2) {
            throw new \RuntimeException('You need at least 2 garments in your wardrobe to get suggestions.');
        }

        $garmentList = $garments->map(function (Garment $garment) {
            $colors = $garment->color_tags ? implode(', ', $garment->color_tags) : 'unknown colors';
            $name = $garment->name ?? $garment->original_filename;
            return "- ID:{$garment->id} | {$name} | Category: {$garment->category->value} | Colors: {$colors} | {$garment->description}";
        })->implode("\n");

        $colorContext = '';
        $colorPalette = $user->color_palette;
        if (!empty($colorPalette)) {
            $colors = implode(', ', $colorPalette);
            $colorContext = "\n\nThe user's personal color palette (colors that flatter them): {$colors}. Prioritize garments in these or complementary colors.\n\n";
        }

        $prompt = "You are a fashion stylist. Given these clothing items from a user's wardrobe:\n\n{$garmentList}{$colorContext}\n\nSuggest {$count} complete outfit combinations for a '{$occasion}' occasion.\n\nFor each outfit:\n1. List the garment IDs to combine\n2. Explain why they work together\n3. Suggest accessories or styling tips\n\nRespond in JSON format:\n[\n  {\n    \"garment_ids\": [1, 2],\n    \"suggestion\": \"Description of why this outfit works...\"\n  }\n]";

        $apiKey = config('services.anthropic.api_key');

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Claude API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to get outfit suggestions from AI.');
        }

        $content = $response->json('content.0.text');

        $jsonMatch = [];
        if (preg_match('/\[.*\]/s', $content, $jsonMatch)) {
            return json_decode($jsonMatch[0], true) ?? [];
        }

        return [];
    }
}
