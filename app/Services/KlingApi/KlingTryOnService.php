<?php

namespace App\Services\KlingApi;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KlingTryOnService
{
    public function __construct(
        private KlingAuthService $authService
    ) {}

    public function submitTryOn(string $modelImagePath, string $garmentImagePath): array
    {
        $token = $this->authService->generateToken();
        $baseUrl = config('services.kling.base_url');

        $humanImageBase64 = $this->preprocessImage($modelImagePath);
        $clothImageBase64 = $this->preprocessImage($garmentImagePath);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($baseUrl . '/v1/images/kolors-virtual-try-on', [
            'model_name' => config('services.kling.tryon_model'),
            'human_image' => $humanImageBase64,
            'cloth_image' => $clothImageBase64,
        ]);

        if (!$response->successful()) {
            Log::error('Kling try-on submission failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Kling API request failed: ' . $response->body());
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
        ])->get($baseUrl . '/v1/images/kolors-virtual-try-on/' . $taskId);

        if (!$response->successful()) {
            Log::error('Kling task status check failed', [
                'task_id' => $taskId,
                'status' => $response->status(),
            ]);
            throw new \RuntimeException('Failed to check task status');
        }

        $data = $response->json('data');

        return [
            'status' => $data['task_status'],
            'images' => $data['task_result']['images'] ?? [],
            'fail_reason' => $data['task_status_msg'] ?? null,
        ];
    }

    private function preprocessImage(string $path): string
    {
        // Read image from storage
        $imageContent = Storage::disk('public')->get($path);
        $image = imagecreatefromstring($imageContent);

        if ($image === false) {
            throw new \RuntimeException("Failed to load image: {$path}");
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // Calculate target dimensions
        $targetWidth = $originalWidth;
        $targetHeight = $originalHeight;

        // Make dimensions even (round up)
        if ($targetWidth % 2 !== 0) {
            $targetWidth++;
        }
        if ($targetHeight % 2 !== 0) {
            $targetHeight++;
        }

        // Scale down if exceeds 2048
        if ($targetWidth > 2048 || $targetHeight > 2048) {
            $scale = min(2048 / $targetWidth, 2048 / $targetHeight);
            $targetWidth = (int) ceil($targetWidth * $scale);
            $targetHeight = (int) ceil($targetHeight * $scale);

            // Ensure still even after scaling
            if ($targetWidth % 2 !== 0) {
                $targetWidth++;
            }
            if ($targetHeight % 2 !== 0) {
                $targetHeight++;
            }
        }

        // Scale up if below 300
        if ($targetWidth < 300 || $targetHeight < 300) {
            $scale = max(300 / $targetWidth, 300 / $targetHeight);
            $targetWidth = (int) ceil($targetWidth * $scale);
            $targetHeight = (int) ceil($targetHeight * $scale);

            // Ensure still even after scaling
            if ($targetWidth % 2 !== 0) {
                $targetWidth++;
            }
            if ($targetHeight % 2 !== 0) {
                $targetHeight++;
            }
        }

        // Create new image with target dimensions
        $processedImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($processedImage === false) {
            imagedestroy($image);
            throw new \RuntimeException("Failed to create processed image");
        }

        // Resample with high quality
        imagecopyresampled(
            $processedImage,
            $image,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            $originalWidth, $originalHeight
        );

        // Encode as JPEG quality 90
        ob_start();
        imagejpeg($processedImage, null, 90);
        $jpegData = ob_get_clean();

        // Free resources
        imagedestroy($image);
        imagedestroy($processedImage);

        return base64_encode($jpegData);
    }
}
