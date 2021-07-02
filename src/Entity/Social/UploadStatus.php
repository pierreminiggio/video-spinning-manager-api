<?php

namespace App\Entity\Social;

use DateTimeInterface;

class UploadStatus
{
    public function __construct(
        public int $id,
        public ?DateTimeInterface $finishedAt,
        public ?string $remoteUrl,
        public ?DateTimeInterface $failedAt
    )
    {
    }
}
