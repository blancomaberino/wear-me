<?php

namespace App\Services\TryOn;

use App\Contracts\TryOnProviderContract;
use App\Contracts\TryOnStatus;
use App\Contracts\TryOnSubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiTryOnProvider implements TryOnProviderContract
{
    /**
     * @param array<int, array{path: string, category: string}> $garments
     */
    public function submitTryOn(string $modelImagePath, array $garments, string $promptHint = '', array $context = []): TryOnSubmission
    {
        $apiKey = config('services.gemini.api_key');
        $endpoint = config('services.gemini.endpoint');

        $modelImageBase64 = $this->prepareImage($modelImagePath);

        // Append measurement context to prompt hint if available
        if (!empty($context['user_measurements']) || !empty($context['garment_measurements'])) {
            $measurementPrompt = '';
            if (!empty($context['user_measurements'])) {
                $um = $context['user_measurements'];
                $parts = [];
                if (!empty($um['height_cm'])) $parts[] = "height {$um['height_cm']}cm";
                if (!empty($um['weight_kg'])) $parts[] = "weight {$um['weight_kg']}kg";
                if (!empty($um['chest_cm'])) $parts[] = "chest {$um['chest_cm']}cm";
                if (!empty($um['waist_cm'])) $parts[] = "waist {$um['waist_cm']}cm";
                if (!empty($um['hips_cm'])) $parts[] = "hips {$um['hips_cm']}cm";
                if (!empty($parts)) {
                    $measurementPrompt .= "The model's body measurements: " . implode(', ', $parts) . '. ';
                }
            }
            if (!empty($context['garment_measurements'])) {
                foreach ($context['garment_measurements'] as $gm) {
                    $gParts = [];
                    if (!empty($gm['size_label'])) $gParts[] = "size {$gm['size_label']}";
                    if (!empty($gm['measurement_chest_cm'])) $gParts[] = "chest: {$gm['measurement_chest_cm']}cm";
                    if (!empty($gm['measurement_waist_cm'])) $gParts[] = "waist: {$gm['measurement_waist_cm']}cm";
                    if (!empty($gm['measurement_length_cm'])) $gParts[] = "length: {$gm['measurement_length_cm']}cm";
                    if (!empty($gm['measurement_inseam_cm'])) $gParts[] = "inseam: {$gm['measurement_inseam_cm']}cm";
                    $category = $gm['category'] ?? 'garment';
                    if (!empty($gParts)) {
                        $measurementPrompt .= "The {$category} is " . implode(', ', $gParts) . '. ';
                    }
                }
            }
            if ($measurementPrompt) {
                $promptHint = trim($promptHint . ' ' . $measurementPrompt . 'Use these measurements to ensure garments fit proportionally and realistically.');
            }
        }

        if (count($garments) === 1) {
            $parts = $this->buildSingleGarmentParts($modelImageBase64, $garments[0], $promptHint);
        } else {
            $style = config('services.gemini.multi_garment_prompt', 'classic');
            $parts = $style === 'interleaved'
                ? $this->buildMultiGarmentPartsInterleaved($modelImageBase64, $garments, $promptHint)
                : $this->buildMultiGarmentParts($modelImageBase64, $garments, $promptHint);
        }

        $response = Http::timeout(120)->post($endpoint . '?key=' . $apiKey, [
            'contents' => [
                [
                    'parts' => $parts,
                ],
            ],
            'generationConfig' => [
                'responseModalities' => ['Text', 'Image'],
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Gemini try-on request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API request failed: ' . $response->body());
        }

        $data = $response->json();
        $imageData = $this->extractImageFromResponse($data);

        $filename = uniqid() . '.jpg';
        $path = 'tryon-results/' . $filename;
        Storage::disk('public')->put($path, base64_decode($imageData));

        return TryOnSubmission::sync($path);
    }

    public function getTaskStatus(string $taskId): TryOnStatus
    {
        throw new \BadMethodCallException('Gemini provider is synchronous and does not support polling.');
    }

    public function isSynchronous(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'gemini';
    }

    private function buildSingleGarmentParts(string $modelImageBase64, array $garment, string $promptHint): array
    {
        $garmentDescription = $promptHint
            ? "the {$promptHint} garment"
            : 'the garment';

        $prompt = implode(' ', [
            "Virtual try-on: Using the person in the first image as the model, dress them in {$garmentDescription} shown in the second image.",
            'Keep the person\'s face, body shape, pose, skin tone, and hair exactly identical to the original photo.',
            'Naturally fit the garment onto the person\'s body with realistic fabric draping, wrinkles, and shadows that match the original lighting.',
            'Maintain the original background, environment, and lighting conditions unchanged.',
            'The result should look like a professional studio photograph, photorealistic, with accurate garment color and texture.',
            'Produce a single clean image with no collage, no split view, no text overlays, and no side-by-side comparison.',
        ]);

        return [
            ['text' => $prompt],
            ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $modelImageBase64]],
            ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $this->prepareImage($garment['path'])]],
        ];
    }

    /**
     * Classic style: single text prompt followed by all images sequentially.
     *
     * @param array<int, array{path: string, category: string}> $garments
     */
    private function buildMultiGarmentParts(string $modelImageBase64, array $garments, string $promptHint): array
    {
        $categoryLabels = [
            'upper' => 'top/upper body clothing',
            'lower' => 'bottom/lower body clothing',
            'dress' => 'full body dress',
        ];

        // Build an explicit checklist of what must appear
        $garmentList = [];
        foreach ($garments as $index => $garment) {
            $label = $categoryLabels[$garment['category']] ?? 'clothing item';
            $garmentList[] = $label;
        }
        $garmentChecklist = implode(' AND ', $garmentList);

        $parts = [];

        // Label the model image
        $parts[] = ['text' => 'This is the model person to dress:'];
        $parts[] = ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $modelImageBase64]];

        // Label each garment image individually
        foreach ($garments as $index => $garment) {
            $label = $categoryLabels[$garment['category']] ?? 'clothing item';
            $parts[] = ['text' => 'This is the ' . $label . ' — the model MUST wear this:'];
            $parts[] = ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $this->prepareImage($garment['path'])]];
        }

        // Final instruction block with strong emphasis on wearing ALL items
        $instructions = [
            "CRITICAL: The model must be wearing BOTH the {$garmentChecklist} in the final image.",
            "Do NOT skip any garment. Every garment image above must appear on the model's body.",
        ];

        if ($promptHint) {
            $instructions[] = "Additional styling instructions: {$promptHint}.";
        }

        $instructions[] = 'Keep the person\'s face, body shape, pose, skin tone, and hair exactly identical to the original photo.';
        $instructions[] = 'Naturally fit all garments onto the person\'s body with realistic fabric draping, wrinkles, and shadows that match the original lighting.';
        $instructions[] = 'Maintain the original background, environment, and lighting conditions unchanged.';
        $instructions[] = 'The result should look like a professional studio photograph, photorealistic, with accurate garment color and texture.';
        $instructions[] = 'Produce a single clean image showing exactly one person wearing ALL garments. No collage, no split view, no text overlays, no side-by-side comparison.';
        $instructions[] = "VERIFICATION: " . count($garments) . " clothing items were provided. Double-check your result — the model must be wearing exactly " . count($garments) . " garments: {$garmentChecklist}. If the model is only wearing " . (count($garments) - 1) . " item, the result is WRONG — redo it with all {$garmentChecklist}.";

        $parts[] = ['text' => implode(' ', $instructions)];

        return $parts;
    }

    /**
     * Interleaved style with numbered garment labels.
     * Enable with GEMINI_MULTI_GARMENT_PROMPT=interleaved
     *
     * @param array<int, array{path: string, category: string}> $garments
     */
    private function buildMultiGarmentPartsInterleaved(string $modelImageBase64, array $garments, string $promptHint): array
    {
        $categoryLabels = [
            'upper' => 'top/upper body clothing',
            'lower' => 'bottom/lower body clothing',
            'dress' => 'full body dress',
        ];

        $parts = [];

        $parts[] = ['text' => 'Virtual try-on task. This is the model person — use their face, body, pose, and background exactly:'];
        $parts[] = ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $modelImageBase64]];

        foreach ($garments as $index => $garment) {
            $label = $categoryLabels[$garment['category']] ?? 'clothing item';
            $num = $index + 1;
            $parts[] = ['text' => "Garment {$num} — this is a {$label} that MUST appear on the model:"];
            $parts[] = ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $this->prepareImage($garment['path'])]];
        }

        $garmentList = [];
        foreach ($garments as $garment) {
            $garmentList[] = $categoryLabels[$garment['category']] ?? 'clothing item';
        }

        $instructions = [
            'CRITICAL: Dress the model in ALL ' . count($garments) . ' garments simultaneously: ' . implode(' AND ', $garmentList) . '.',
            'Do NOT skip any garment. Every single garment shown above must be visible on the model.',
        ];

        if ($promptHint) {
            $instructions[] = "Additional styling instructions: {$promptHint}.";
        }

        $instructions[] = 'Output exactly ONE person — the same model from the first image — wearing all the garments together.';
        $instructions[] = 'Keep the person\'s face, body shape, pose, skin tone, and hair exactly identical to the original photo.';
        $instructions[] = 'Naturally fit all garments onto the person\'s body with realistic fabric draping, wrinkles, and shadows that match the original lighting.';
        $instructions[] = 'Maintain the original background, environment, and lighting conditions unchanged.';
        $instructions[] = 'The result should look like a professional studio photograph, photorealistic, with accurate garment color and texture.';
        $instructions[] = 'Produce a single clean image showing only ONE person wearing ALL garments. No collage, no split view, no text overlays, no side-by-side comparison.';
        $instructions[] = 'VERIFICATION: ' . count($garments) . ' clothing items were provided. Double-check your result — the model must be wearing exactly ' . count($garments) . ' garments: ' . implode(' AND ', $garmentList) . '. If the model is only wearing ' . (count($garments) - 1) . ' item, the result is WRONG — redo it with all items.';

        $parts[] = ['text' => implode(' ', $instructions)];

        return $parts;
    }

    private function prepareImage(string $path): string
    {
        $imageContent = Storage::disk('public')->get($path);

        if ($imageContent === null) {
            throw new \RuntimeException("Failed to read image: {$path}");
        }

        return base64_encode($imageContent);
    }

    private function extractImageFromResponse(array $data): string
    {
        $candidates = $data['candidates'] ?? [];

        foreach ($candidates as $candidate) {
            $parts = $candidate['content']['parts'] ?? [];
            foreach ($parts as $part) {
                if (isset($part['inlineData']['data'])) {
                    return $part['inlineData']['data'];
                }
                if (isset($part['inline_data']['data'])) {
                    return $part['inline_data']['data'];
                }
            }
        }

        throw new \RuntimeException('No image found in Gemini response');
    }
}
