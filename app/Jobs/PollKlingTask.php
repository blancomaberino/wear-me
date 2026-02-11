<?php

namespace App\Jobs;

use App\Enums\ProcessingStatus;
use App\Models\TryOnVideo;
use App\Services\KlingApi\KlingVideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PollKlingTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 30;

    public function __construct(
        private TryOnVideo $tryOnVideo
    ) {}

    public function handle(KlingVideoService $videoService): void
    {
        try {
            $taskId = $this->tryOnVideo->kling_task_id;
            $result = $videoService->getTaskStatus($taskId);
            $status = $result['status'];

            if ($status === 'succeed') {
                $this->handleSuccess($result);
            } elseif ($status === 'failed') {
                $failReason = $result['fail_reason'] ?? 'Kling video processing failed';
                $this->tryOnVideo->update([
                    'status' => ProcessingStatus::Failed,
                    'error_message' => $failReason,
                ]);
            } else {
                self::dispatch($this->tryOnVideo)
                    ->delay(now()->addSeconds(10));
            }

        } catch (\Throwable $e) {
            Log::error('PollKlingTask failed', [
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->tryOnVideo->update([
                    'status' => ProcessingStatus::Failed,
                    'error_message' => 'Polling timed out: ' . $e->getMessage(),
                ]);
            }
        }
    }

    private function handleSuccess(array $result): void
    {
        if (!empty($result['videos'])) {
            $videoUrl = $result['videos'][0]['url'];
            $duration = $result['videos'][0]['duration'] ?? null;

            $response = Http::timeout(60)->get($videoUrl);
            $filename = uniqid() . '.mp4';
            $path = 'tryon-videos/' . $filename;

            Storage::disk('public')->put($path, $response->body());

            $this->tryOnVideo->update([
                'status' => ProcessingStatus::Completed,
                'video_path' => $path,
                'duration_seconds' => $duration,
            ]);
        }
    }
}
