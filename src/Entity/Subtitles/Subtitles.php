<?php

namespace App\Entity\Subtitles;

class Subtitles
{
    public function __construct(
        private float $startTime,
        private float $endTime,
        private string $text
    )
    {
    }
}
