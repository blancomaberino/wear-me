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
                OutfitSuggestion::create([
                    'user_id' => $this->user->id,
                    'garment_ids' => $suggestion['garment_ids'] ?? [],
                    'suggestion_text' => $suggestion['suggestion'] ?? '',
                    'occasion' => $this->occasion,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GenerateOutfitSuggestion failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
