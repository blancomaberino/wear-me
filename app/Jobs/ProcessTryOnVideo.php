<?php

namespace App\Jobs;

use App\Models\TryOnVideo;
use App\Enums\ProcessingStatus;
use App\Services\KlingApi\KlingVideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTryOnVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private TryOnVideo $tryOnVideo
    ) {}

    public function handle(KlingVideoService $videoService): void
    {
        try {
            $this->tryOnVideo->update(['status' => ProcessingStatus::Processing]);

            $sourceUrl = $this->tryOnVideo->tryonResult
                ? url($this->tryOnVideo->tryonResult->result_url)
                : url($this->tryOnVideo->modelImage->url);

            $result = $videoService->submitVideoGeneration($sourceUrl);

            $this->tryOnVideo->update([
                'kling_task_id' => $result['task_id'],
            ]);

            PollKlingTask::dispatch($this->tryOnVideo)
                ->delay(now()->addSeconds(15));

        } catch (\Throwable $e) {
            Log::error('ProcessTryOnVideo failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->tryOnVideo->update([
                'status' => ProcessingStatus::Failed,
                'error_message' => 'Video processing failed. Please try again.',
            ]);
        }
    }
}
