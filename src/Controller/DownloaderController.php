<?php

namespace App\Controller;

use App\Query\QueryWithIdParameter;
use PierreMiniggio\MP4YoutubeVideoDownloader\Downloader;
use Throwable;

class DownloaderController
{
    public function __construct(
        private QueryWithIdParameter $query,
        private string               $cacheFolder,
        private Downloader           $downloader
    )
    {
    }

    public function __invoke(int $id): void
    {
        $link = $this->query->execute($id);

        if ($link === null) {
            http_response_code(404);

            return;
        }

        if (! file_exists($this->cacheFolder)) {
            mkdir($this->cacheFolder);
        }

        $filename = $this->cacheFolder . $id . '.mp4';

        if (file_exists($filename)) {
            http_response_code(204);
            return;
        }

        set_time_limit(0);

        try {
            $this->downloader->download($link, $filename);
        } catch (Throwable $e) {
            shell_exec('youtube-dl https://youtu.be/' . substr($link, strlen('https://www.youtube.com/watch?v=')) . ' -f mp4 --output ' . $filename);
        }

        http_response_code(204);
    }
}
