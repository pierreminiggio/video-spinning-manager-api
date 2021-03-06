<?php

namespace App\Query\Content;

use App\Entity\ToProcess;
use App\Entity\ToProcessDetail;
use App\Entity\Video\Video;
use App\Query\QueryWithIdParameter;
use DateTime;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class ToProcessDetailQuery implements QueryWithIdParameter
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(int $id): ?ToProcessDetail
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
            ->where(
                'sc.id = :id'
            ),
            ['id' => $id]
        );
        
        if (! $querieds) {
            return null;
        }

        $queried = $querieds[0];
        $content = new ToProcess(
            (int) $queried['id'],
            $queried['title'],
            $queried['url']
        );

        $queriedVideos = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video'
            )->select(
                'id, name, width, height, fps, finished_at'
            )->where(
                'content_id = :id'
            ),
            ['id' => $id]
        );

        $videos = array_map(fn (array $queriedVideo): Video => new Video(
            (int) $queriedVideo['id'],
            $queriedVideo['name'],
            (int) $queriedVideo['width'],
            (int) $queriedVideo['height'],
            (int) $queriedVideo['fps'],
            $queriedVideo['finished_at'] ? new DateTime($queriedVideo['finished_at']) : null
        ), $queriedVideos);

        return new ToProcessDetail($content, $videos);
    }
}
