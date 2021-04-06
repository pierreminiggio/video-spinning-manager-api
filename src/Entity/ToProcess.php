<?php

namespace App\Entity;

class ToProcess
{
    public function __construct(
        public int $id,
        public string $title,
        public string $url
    )
    {
    }
}
