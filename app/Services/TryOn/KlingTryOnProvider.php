<?php

namespace App\Services\TryOn;

use App\Contracts\TryOnProviderContract;
use App\Contracts\TryOnStatus;
use App\Contracts\TryOnSubmission;
use App\Services\KlingApi\KlingTryOnService;

class KlingTryOnProvider implements TryOnProviderContract
{
    public function __construct(
        private KlingTryOnService $klingService
    ) {}

    /**
     * @param array<int, array{path: string, category: string}> $garments
     */
    public function submitTryOn(string $modelImagePath, array $garments, string $promptHint = '', array $context = []): TryOnSubmission
    {
        // Kling only supports single garment — use the first one
        $garmentPath = $garments[0]['path'];
        $result = $this->klingService->submitTryOn($modelImagePath, $garmentPath);

        return TryOnSubmission::async($result['task_id']);
    }

    public function getTaskStatus(string $taskId): TryOnStatus
    {
        $result = $this->klingService->getTaskStatus($taskId);

        return new TryOnStatus(
            status: $result['status'],
            images: $result['images'],
            failReason: $result['fail_reason'] ?? null,
        );
    }

    public function isSynchronous(): bool
    {
        return false;
    }

    public function name(): string
    {
        return 'kling';
    }
}
