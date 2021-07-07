<?php

namespace App\Query\Account\TikTok;

use App\Entity\Social\TikTok\Video;
use App\Query\QueryWithIdParameter;
use App\Query\Render\CurrentRenderStatusForVideoQuery;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideoFileQuery implements QueryWithIdParameter
{
    public function __construct(
        private DatabaseFetcher                  $fetcher,
        private CurrentRenderStatusForVideoQuery $renderCheckQuery
    )
    {
    }

    public function execute(int $tiktokId): ?Video
    {
        $querieds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_tiktok_upload',
                'tu'
            )->select(
                'tu.video_id'
            )->where(
                'id = :id'
            ),
            ['id' => $tiktokId]
        );
        
        if (! $querieds) {
            return null;
        }

        $queried = $querieds[0];
        $videoId = (int) $queried['video_id'];
        $renderStatus = $this->renderCheckQuery->execute($videoId);
      
        return new Video(
            $videoId,
            $renderStatus !== null && $renderStatus->hasRenderedFile()
        );
    }
}
