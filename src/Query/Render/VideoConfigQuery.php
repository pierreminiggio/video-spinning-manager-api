<?php

namespace App\Query\Render;

use App\Entity\Render\VideoConfig;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideoConfigQuery
{
    public function __construct(
        private DatabaseFetcher $fetcher
    )
    {
    }

    public function execute(int $videoId): ?VideoConfig
    {
        $queriedEntities = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video'
            )->select(
                'width, height, fps'
            )->where(
                'id = :id'
            ),
            ['id' => $videoId]
        );
        
        if (! $queriedEntities) {
            return null;
        }

        $queriedEntity = $queriedEntities[0];

        return new VideoConfig(
            (int) $queriedEntity['width'],
            (int) $queriedEntity['height'],
            (int) $queriedEntity['fps']
        );
    }
}
