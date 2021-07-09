<?php

namespace App\Entity\Clear;

class DoneVideo
{
    public function __construct(
        public int $contentId,
        public ?int $videoId
    )
    {
    }
}
