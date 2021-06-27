<?php

namespace App\Query\Editor;

use NeutronStars\Database\Query;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class CurrentStateQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(int $videoId): ?string
    {
        $queriedEntities = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video_editor_state'
            )->select(
                'clip_maker_props'
            )->where(
                'video_id = :video_id'
            )->orderBy(
                'created_at',
                Query::ORDER_BY_DESC
            )->limit(1),
            ['video_id' => $videoId]
        );

        if (! $queriedEntities) {
            return null;
        }

        return $queriedEntities[0]['clip_maker_props'];
    }
}