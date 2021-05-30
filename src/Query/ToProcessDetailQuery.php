<?php

namespace App\Query;

use App\Entity\ToProcess;
use App\Entity\ToProcessDetail;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class ToProcessDetailQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function findById(int $id): ?ToProcessDetail
    {
        $querieds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content as sc'
            )->select(
                'sc.id as id, yv.title as title, yv.url as url'
            )->join(
                'spinned_content_youtube_video as scyv',
                'sc.id = scyv.spinned_id'
            )->join(
                'youtube_video as yv',
                'yv.id = scyv.youtube_id'
            )
            ->where('sc.id = :id'),
            ['id' => $id]
        );
        
        if (! $querieds) {
            return null;
        }

        $queried = $querieds[0];

        return new ToProcessDetail(new ToProcess(
            $queried['id'],
            $queried['title'],
            $queried['url']
        ));
    }
}
