<?php

namespace App\Jobs;

use App\Contracts\TryOnProviderContract;
use App\Enums\ProcessingStatus;
use App\Models\TryOnResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PollProviderTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 30;

    public function __construct(
        private TryOnResult $tryOnResult
    ) {}

    public function handle(TryOnProviderContract $provider): void
    {
        try {
            $taskId = $this->tryOnResult->provider_task_id;
            $result = $provider->getTaskStatus($taskId);

            if ($result->status === 'succeed') {
                $this->handleSuccess($result->images);
            } elseif ($result->status === 'failed') {
                $this->tryOnResult->update([
                    'status' => ProcessingStatus::Failed,
                    'error_message' => $result->failReason ?? 'Provider processing failed',
                ]);
            } else {
                self::dispatch($this->tryOnResult)
                    ->delay(now()->addSeconds(10));
            }

        } catch (\Throwable $e) {
            Log::error('PollProviderTask failed', [
                'provider' => $this->tryOnResult->provider,
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->tryOnResult->update([
                    'status' => ProcessingStatus::Failed,
                    'error_message' => 'Processing timed out. Please try again.',
                ]);
            }
        }
    }

    private function validateExternalUrl(string $url): void
    {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? '';
        $host = $parsed['host'] ?? '';

        if ($scheme !== 'https') {
            throw new \RuntimeException('Only HTTPS URLs are allowed for downloads');
        }

        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            throw new \RuntimeException('URL resolves to a private or reserved IP address');
        }
    }

    private function handleSuccess(array $images): void
    {
        if (empty($images)) {
            $this->tryOnResult->update([
                'status' => ProcessingStatus::Failed,
                'error_message' => 'No images in provider response',
            ]);
            return;
        }

        $imageUrl = $images[0]['url'];
        $this->validateExternalUrl($imageUrl);
        $response = Http::timeout(60)->get($imageUrl);
        $filename = Str::random(20) . '.jpg';
        $path = 'tryon-results/' . $filename;

        Storage::disk('public')->put($path, $response->body());

        $this->tryOnResult->update([
            'status' => ProcessingStatus::Completed,
            'result_path' => $path,
        ]);
    }
}
