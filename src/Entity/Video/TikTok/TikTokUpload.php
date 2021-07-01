<?php

namespace App\Entity\Video\TikTok;

class TikTokUpload
{
    public function __construct(
        public int $id,
        public int $videoId,
        public int $accountId,
        public string $legend
    )
    {
    }
}
