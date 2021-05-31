<?php

namespace App\Entity;

use App\Entity\Video\Video;

class ToProcessDetail
{

    /**
     * @param Video[] $videos
     */
    public function __construct(
        public ToProcess $content,
        public array $videos
    )
    {
    }
}
