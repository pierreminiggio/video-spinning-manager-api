<?php

namespace App\Entity\Social\TikTok;

class Video
{
    public function __construct(
        public int $id,
        public bool $hasRenderedFile,
    )
    {
    }
}
