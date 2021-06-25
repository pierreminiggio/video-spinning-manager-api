<?php

namespace App\Command\Video;

use Exception;
use DateTimeInterface;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class FinishCommand
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    /**
     * @throws Exception
     */
    public function execute(int $videoId, DateTimeInterface $finishedAt): void
    {
        $this->fetcher->exec(
            $this->fetcher->createQuery(
                'spinned_content_video'
            )->update(
                'finished_at = :finished_at'
            )->where(
                'id = :id'
            ),
            [
                'finished_at' => $finishedAt->format('Y-m-d H:i:s'),
                'id' => $videoId
            ]
        );
    }
}
