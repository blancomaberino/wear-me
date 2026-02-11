<?php

namespace App\Contracts;

class TryOnSubmission
{
    public function __construct(
        public readonly bool $isComplete,
        public readonly ?string $taskId,
        public readonly ?string $resultPath,
    ) {}

    public static function async(string $taskId): self
    {
        return new self(
            isComplete: false,
            taskId: $taskId,
            resultPath: null,
        );
    }

    public static function sync(string $resultPath): self
    {
        return new self(
            isComplete: true,
            taskId: null,
            resultPath: $resultPath,
        );
    }
}
