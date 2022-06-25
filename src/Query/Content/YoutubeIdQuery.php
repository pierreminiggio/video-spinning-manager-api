<?php

namespace App\Query\Content;

use App\Query\QueryWithIdParameter;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class YoutubeIdQuery implements QueryWithIdParameter
{

    public function __construct(
        private DatabaseFetcher $fetcher
    )
    {
    }

    public function execute(int $id): ?string
    {
        $querieds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content as sc'
            )->select(
                'sc.id as id, yv.youtube_id as youtube_id'
            )->join(
                'spinned_content_youtube_video as scyv',
                'sc.id = scyv.spinned_id'
            )->join(
                'youtube_video as yv',
                'yv.id = scyv.youtube_id'
            )
            ->where(
                'sc.id = :id'
            ),
            ['id' => $id]
        );
        
        if (! $querieds) {
            return null;
        }

        $queried = $querieds[0];

        return $queried['youtube_id'] ?? null;
    }
}
