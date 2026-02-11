<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ColorPaletteService
{
    public function detectFromImage(string $imagePath, int $colorCount = 8): array
    {
        $imageContents = Storage::disk('public')->get($imagePath);

        if (!$imageContents) {
            throw new \RuntimeException('Could not read image file.');
        }

        $base64Image = base64_encode($imageContents);

        $mimeType = 'image/jpeg';
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if ($extension === 'png') {
            $mimeType = 'image/png';
        } elseif ($extension === 'webp') {
            $mimeType = 'image/webp';
        }

        $apiKey = config('services.anthropic.api_key');

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => max(512, $colorCount * 30),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mimeType,
                                'data' => $base64Image,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => "Analyze this person's natural coloring (skin tone, hair color, eye color). Based on color theory and seasonal color analysis, recommend exactly {$colorCount} flattering colors for them to wear. IMPORTANT: Cover the FULL color spectrum — distribute colors evenly across reds, oranges, yellows, greens, blues, purples, pinks, and neutrals (beige, brown, grey, black, white). Each color must be visually distinct from every other. Vary hues, saturations, and lightnesses. Do NOT cluster around similar shades. Respond with ONLY a raw JSON array of hex color codes. No markdown, no code fences, no explanation.",
                        ],
                    ],
                ],
                [
                    'role' => 'assistant',
                    'content' => '[',
                ],
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Claude Vision API request failed for color palette detection', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to detect color palette from image.');
        }

        $content = '[' . $response->json('content.0.text');

        // Strip markdown code fences if present
        $content = preg_replace('/```(?:json)?\s*/i', '', $content);
        $content = trim($content);

        // Try parsing as complete JSON array first
        $jsonMatch = [];
        if (preg_match('/\[.*\]/s', $content, $jsonMatch)) {
            $colors = json_decode($jsonMatch[0], true);

            if (is_array($colors)) {
                $valid = array_values(array_filter($colors, function ($color) {
                    return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
                }));
                if (!empty($valid)) {
                    return $valid;
                }
            }
        }

        // Fallback: extract individual hex codes (handles truncated responses)
        $hexMatches = [];
        if (preg_match_all('/#[0-9A-Fa-f]{6}/', $content, $hexMatches)) {
            $valid = array_values(array_unique($hexMatches[0]));
            if (!empty($valid)) {
                return $valid;
            }
        }

        Log::warning('Could not parse color palette from Claude response', ['content' => $content]);
        throw new \RuntimeException('Could not parse color palette from AI response.');
    }
}
