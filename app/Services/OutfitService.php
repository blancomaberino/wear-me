<?php

namespace App\Services;

use App\Models\Outfit;
use App\Models\User;

class OutfitService
{
    public function __construct(
        private ColorHarmonyService $harmonyService,
    ) {}

    public function createOutfit(User $user, array $data, array $garmentData): Outfit
    {
        $outfit = $user->outfits()->create([
            'name' => $data['name'],
            'occasion' => $data['occasion'] ?? null,
            'notes' => $data['notes'] ?? null,
            'outfit_template_id' => $data['outfit_template_id'] ?? null,
        ]);

        // Attach garments with slot info
        $pivotData = [];
        foreach ($garmentData as $index => $item) {
            $pivotData[$item['garment_id']] = [
                'slot_label' => $item['slot_label'] ?? null,
                'sort_order' => $index,
            ];
        }
        $outfit->garments()->attach($pivotData);

        // Compute harmony score from garment colors
        $outfit->load('garments');
        $colors = $outfit->garments->pluck('color_tags')->flatten()->filter()->unique()->values()->all();
        if (count($colors) >= 2) {
            $score = $this->harmonyService->computeScore($colors);
            $outfit->update(['harmony_score' => $score]);
        }

        return $outfit;
    }
}
