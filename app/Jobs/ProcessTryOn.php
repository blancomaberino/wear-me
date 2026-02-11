<?php

namespace App\Jobs;

use App\Models\TryOnResult;
use App\Enums\ProcessingStatus;
use App\Contracts\TryOnProviderContract;
use App\Services\TryOnService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTryOn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private TryOnResult $tryOnResult
    ) {}

    public function handle(TryOnProviderContract $provider): void
    {
        try {
            $this->tryOnResult->update([
                'status' => ProcessingStatus::Processing,
                'provider' => $provider->name(),
            ]);

            if ($this->tryOnResult->source_tryon_result_id) {
                $modelImagePath = $this->tryOnResult->sourceResult->result_path;
            } else {
                $modelImagePath = $this->tryOnResult->modelImage->path;
            }

            // Load garments from pivot table, fallback to legacy garment_id
            $tryOnService = app(TryOnService::class);
            $garments = $tryOnService->resolveGarments($this->tryOnResult);

            $garmentData = $garments->map(fn ($g) => [
                'path' => $g->path,
                'category' => $g->category->value,
            ])->all();

            $promptHint = $this->tryOnResult->prompt_hint ?? '';

            // Build measurement context for enhanced prompts
            $context = [];
            $user = $this->tryOnResult->user;
            if ($user && $user->hasMeasurements()) {
                $context['user_measurements'] = [
                    'height_cm' => $user->height_cm,
                    'weight_kg' => $user->weight_kg,
                    'chest_cm' => $user->chest_cm,
                    'waist_cm' => $user->waist_cm,
                    'hips_cm' => $user->hips_cm,
                ];
            }

            $garmentMeasurements = [];
            foreach ($garments as $g) {
                if ($g->size_label || $g->measurement_chest_cm || $g->measurement_waist_cm) {
                    $garmentMeasurements[] = [
                        'category' => $g->category->value,
                        'size_label' => $g->size_label,
                        'measurement_chest_cm' => $g->measurement_chest_cm,
                        'measurement_waist_cm' => $g->measurement_waist_cm,
                        'measurement_length_cm' => $g->measurement_length_cm,
                        'measurement_inseam_cm' => $g->measurement_inseam_cm,
                    ];
                }
            }
            if (!empty($garmentMeasurements)) {
                $context['garment_measurements'] = $garmentMeasurements;
            }

            $submission = $provider->submitTryOn($modelImagePath, $garmentData, $promptHint, $context);

            if ($submission->isComplete) {
                $this->tryOnResult->update([
                    'status' => ProcessingStatus::Completed,
                    'result_path' => $submission->resultPath,
                ]);
            } else {
                $this->tryOnResult->update([
                    'provider_task_id' => $submission->taskId,
                ]);

                PollProviderTask::dispatch($this->tryOnResult)
                    ->delay(now()->addSeconds(10));
            }

        } catch (\Throwable $e) {
            Log::error('ProcessTryOn failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->tryOnResult->update([
                'status' => ProcessingStatus::Failed,
                'error_message' => 'Try-on processing failed. Please try again.',
            ]);
        }
    }
}
