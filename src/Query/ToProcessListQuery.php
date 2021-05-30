<?php

namespace App\Query;

use App\Entity\ToProcess;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class ToProcessListQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    /**
     * @return ToProcess[]
     */
    public function findAll(): array
    {
        $queried = $this->fetcher->query(
            $this->fetcher
                ->createQuery('spinned_content as sc')
                ->select('sc.id as id, yv.title as title, yv.url as url')
                ->join(
                    'spinned_content_youtube_video as scyv',
                    'sc.id = scyv.spinned_id'
                )
                ->join(
                    'youtube_video as yv',
                    'yv.id = scyv.youtube_id'
                )
                ->where('sc.spinned = 0')
                ->orderBy('yv.created_at')
        );

        if (! $queried) {
            return [];
        }

        return array_map(fn (array $entry): ToProcess => new ToProcess(
            $entry['id'],
            $entry['title'],
            $entry['url']
        ), $queried);
    }
}
