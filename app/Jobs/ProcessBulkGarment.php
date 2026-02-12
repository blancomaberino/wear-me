<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\WardrobeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessBulkGarment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        private User $user,
        private string $tempPath,
        private string $originalName,
        private string $category
    ) {}

    public function handle(WardrobeService $wardrobeService): void
    {
        $fullPath = Storage::disk('local')->path($this->tempPath);

        if (!file_exists($fullPath)) {
            Log::warning('ProcessBulkGarment: temp file not found', ['path' => $this->tempPath]);
            return;
        }

        try {
            $file = new UploadedFile($fullPath, $this->originalName, null, null, true);

            $wardrobeService->storeGarment(
                $this->user,
                ['category' => $this->category],
                $file
            );
        } catch (\Throwable $e) {
            Log::error('ProcessBulkGarment failed', [
                'user_id' => $this->user->id,
                'temp_path' => $this->tempPath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            if (file_exists($fullPath)) {
                Storage::disk('local')->delete($this->tempPath);
            }
        }
    }
}
