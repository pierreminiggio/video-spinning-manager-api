<?php

namespace App\Query\Clear;

use App\Entity\Clear\DoneVideo;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class DoneVideoQuery
{
    public function __construct(
        private DatabaseFetcher $fetcher
    )
    {
    }

    /**
     * @return DoneVideo[] videos ids
     */
    public function execute(): array
    {
        $fetchedVideos = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content',
                'sc'
            )->join(
                'spinned_content_video as scv',
                'scv.content_id = sc.id'
            )->select(
                'sc.id as content_id',
                'scv.id as video_id'
            )->where(
                'sc.spinned = 1'
            )
        );

        return array_map(fn (array $fetchedVideo): DoneVideo => new DoneVideo(
            (int) $fetchedVideo['content_id'],
            $fetchedVideo['video_id'] ? (int) $fetchedVideo['video_id'] : null
        ), $fetchedVideos);
    }
}