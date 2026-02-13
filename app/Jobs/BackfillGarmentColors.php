<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\GarmentColorExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BackfillGarmentColors implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;
    public int $uniqueFor = 300;

    public function __construct(
        private User $user
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->user->id;
    }

    public function handle(GarmentColorExtractor $extractor): void
    {
        $total = 0;
        $processed = 0;
        $failed = 0;

        $this->user->garments()
            ->where(function ($query) {
                $query->whereNull('color_tags')
                    ->orWhere('color_tags', '[]')
                    ->orWhere('color_tags', 'null');
            })
            ->chunk(50, function ($garments) use ($extractor, &$total, &$processed, &$failed) {
                foreach ($garments as $garment) {
                    $total++;
                    try {
                        $colors = $extractor->extract($garment->path);
                        if (!empty($colors)) {
                            $garment->update(['color_tags' => $colors]);
                            $processed++;
                        }
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::warning('BackfillGarmentColors: Failed to process garment', [
                            'garment_id' => $garment->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        Log::info('BackfillGarmentColors: Completed', [
            'user_id' => $this->user->id,
            'total' => $total,
            'processed' => $processed,
            'failed' => $failed,
        ]);
    }
}
