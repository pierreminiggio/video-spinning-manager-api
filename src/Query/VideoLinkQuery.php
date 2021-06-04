<?php

namespace App\Query;

use App\Query\Query;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideoLinkQuery implements Query
{
    public function __construct(
        private DatabaseFetcher $fetcher,
    )
    {
    }

    public function execute(int $id): ?string
    {
        $querieds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content',
                'sc'
            )->join(
                'spinned_content_youtube_video as scyv',
                'sc.id = scyv.spinned_id'
            )->join(
                'youtube_video as yv',
                'yv.id = scyv.youtube_id'
            )->select(
                'yv.url'
            )->where(
                'sc.id = :id'
            ),
            ['id' => $id]
        );
        
        if (! $querieds) {
            return null;
        }

        return $querieds[0]['url'];
    }
}