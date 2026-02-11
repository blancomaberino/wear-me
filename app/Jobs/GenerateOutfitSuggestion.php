<?php

namespace App\Jobs;

use App\Models\OutfitSuggestion;
use App\Models\User;
use App\Services\OutfitSuggestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateOutfitSuggestion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        private User $user,
        private string $occasion = 'casual'
    ) {}

    public function handle(OutfitSuggestionService $service): void
    {
        try {
            $suggestions = $service->generateSuggestions($this->user, $this->occasion);

            foreach ($suggestions as $suggestion) {
                $outfitSuggestion = $this->user->outfitSuggestions()->create([
                    'garment_ids' => $suggestion['garment_ids'] ?? [],
                    'suggestion_text' => $suggestion['suggestion'] ?? '',
                    'occasion' => $this->occasion,
                ]);

                // Attach garments to pivot table (only those that exist)
                $garmentIds = $suggestion['garment_ids'] ?? [];
                if (!empty($garmentIds)) {
                    $existingIds = $this->user->garments()->whereIn('id', $garmentIds)->pluck('id')->all();
                    $pivotData = [];
                    foreach ($garmentIds as $index => $garmentId) {
                        if (in_array($garmentId, $existingIds)) {
                            $pivotData[$garmentId] = ['sort_order' => $index];
                        }
                    }
                    if (!empty($pivotData)) {
                        $outfitSuggestion->garments()->attach($pivotData);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('GenerateOutfitSuggestion failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
