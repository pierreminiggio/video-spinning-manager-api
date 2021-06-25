<?php

namespace App\Entity\Video;

class VideoDetail
{
    public function __construct(
        public Video $video,
        public bool $downloaded,
        public EditorState $editorState
    )
    {
    }
}
