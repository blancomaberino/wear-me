<?php

namespace App\Services\KlingApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KlingVideoService
{
    public function __construct(
        private KlingAuthService $authService
    ) {}

    public function submitVideoGeneration(string $imageUrl, string $prompt = 'A person naturally posing and moving'): array
    {
        $token = $this->authService->generateToken();
        $baseUrl = config('services.kling.base_url');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($baseUrl . '/v1/videos/image2video', [
            'image' => $imageUrl,
            'prompt' => $prompt,
            'duration' => '5',
        ]);

        if (!$response->successful()) {
            Log::error('Kling video submission failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Kling video API request failed: ' . $response->body());
        }

        $data = $response->json();

        return [
            'task_id' => $data['data']['task_id'],
            'status' => $data['data']['task_status'] ?? 'submitted',
        ];
    }

    public function getTaskStatus(string $taskId): array
    {
        $token = $this->authService->generateToken();
        $baseUrl = config('services.kling.base_url');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get($baseUrl . '/v1/videos/image2video/' . $taskId);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to check video task status');
        }

        $data = $response->json('data');

        return [
            'status' => $data['task_status'],
            'videos' => $data['task_result']['videos'] ?? [],
        ];
    }
}
