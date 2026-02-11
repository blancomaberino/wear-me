<?php

namespace App\Contracts;

class TryOnStatus
{
    public function __construct(
        public readonly string $status,
        public readonly array $images,
        public readonly ?string $failReason,
    ) {}
}
