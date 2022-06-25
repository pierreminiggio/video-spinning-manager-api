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

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getEndTime(): float
    {
        return $this->endTime;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
