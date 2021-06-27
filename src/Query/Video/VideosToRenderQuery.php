<?php

namespace App\Query\Video;

use NeutronStars\Database\Query;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideosToRenderQuery
{
    public function __construct(
        private DatabaseFetcher $fetcher
    )
    {
    }

    /**
     * @return int[]
     */
    public function execute(): array
    {
        $orderedRenderStatusQuery = $this->fetcher->createQuery(
            $this->fetcher->createQuery(
                'spinned_content_video_render_status'
            )->select(
                'video_id',
                'max(id) as last_id'
            )->groupBy(
                'video_id'
            ),
            'lrs'
        )->select(
            'lrs.last_id as id',
            'lrs.video_id',
            'rs.finished_at',
            'rs.failed_at'
        )->join(
            'spinned_content_video_render_status as rs',
            'rs.id = lrs.last_id'
        );

        $queriedVideos = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video',
                'v'
            )->join(
                '(' . $orderedRenderStatusQuery->build() . ') as crs',
                'crs.video_id = v.id'
            )->select(
                'v.id'
            )->where(
                'v.finished_at IS NOT NULL AND ((crs.finished_at IS NULL AND crs.failed_at IS NOT NULL) OR crs.id IS NULL)'
            )->orderBy(
                'v.finished_at'
            )
        );

        return array_map(fn (array $queriedVideo) => (int) $queriedVideo['id'], $queriedVideos);
    }
}