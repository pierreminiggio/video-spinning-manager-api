<?php

namespace App\Entity\Render;

class VideoConfig
{
    public function __construct(
        public int $width,
        public int $height,
        public int $fps
    )
    {
    }
}
