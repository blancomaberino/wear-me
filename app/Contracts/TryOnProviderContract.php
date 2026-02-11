<?php

namespace App\Contracts;

interface TryOnProviderContract
{
    /**
     * @param array<int, array{path: string, category: string}> $garments
     */
    public function submitTryOn(string $modelImagePath, array $garments, string $promptHint = '', array $context = []): TryOnSubmission;

    public function getTaskStatus(string $taskId): TryOnStatus;

    public function isSynchronous(): bool;

    public function name(): string;
}
