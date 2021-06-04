<?php

namespace App\Controller;

class ThumbnailController
{

    public function __construct(
        private string $cacheFolder
    )
    {
    }

    public function __invoke(int $id, int $seconds): void
    {
        header("Content-Type: image/png");
        readfile($this->getThumbnailPath($id, $seconds));
    }

    protected function getThumbnailPath(int $videoId, int $seconds): string
    {
        $filename = "$videoId-$seconds.png";
        $filePath = $this->cacheFolder . $filename;

        if (file_exists($filePath)) {
            return $filePath;
        }

        shell_exec('ffmpeg -i ' . $this->cacheFolder . $videoId . '.mp4 -vframes 1 -an -s 1280x720 -ss ' . $seconds . ' ' . $filePath);

        return $filePath;
    }
}
