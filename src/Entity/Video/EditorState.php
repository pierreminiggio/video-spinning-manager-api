<?php

namespace App\Entity\Video;

class EditorState
{
    public function __construct(
        public array $clips,
        public array $texts,
    )
    {
    }
}
