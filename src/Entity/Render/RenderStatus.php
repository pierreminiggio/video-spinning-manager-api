<?php

namespace App\Entity\Render;

use DateTimeInterface;

class RenderStatus
{
    public function __construct(
        public int $id,
        public ?DateTimeInterface $finishedAt,
        public ?string $filePath,
        public ?DateTimeInterface $failedAt,
        public ?string $failedReason
    )
    {
    }
}
