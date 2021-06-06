<?php

namespace App\Entity\Video;

class Video
{
    public function __construct(
        public int $id,
        public string $name,
        public int $width,
        public int $height
    )
    {
    }
}
