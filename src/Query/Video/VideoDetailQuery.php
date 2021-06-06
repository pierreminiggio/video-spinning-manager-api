<?php

namespace App\Query\Video;

use App\Entity\Video\Video;
use App\Entity\Video\VideoDetail;
use App\Query\Query;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideoDetailQuery implements Query
{
    public function __construct(
        private DatabaseFetcher $fetcher,
        private string $cacheFolder
    )
    {
    }

    public function execute(int $id): ?VideoDetail
    {
        $querieds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video'
            )->select(
                'id, name, width, height'
            )->where(
                'id = :id'
            ),
            ['id' => $id]
        );
        
        if (! $querieds) {
            return null;
        }

        $queried = $querieds[0];
        $videoId = (int) $queried['id'];
        $video = new Video(
            $videoId,
            $queried['name'],
            $queried['width'],
            $queried['height']
        );

        return new VideoDetail($video, file_exists($this->cacheFolder . (int) $queried['content_id'] . '.mp4'));
    }
}
