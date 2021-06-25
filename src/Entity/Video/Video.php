<?php

namespace App\Entity\Video;

use DateTimeInterface;

class Video
{
    public function __construct(
        public int $id,
        public string $name,
        public int $width,
        public int $height,
        public ?DateTimeInterface $finishedAt
    )
    {
    }
}
