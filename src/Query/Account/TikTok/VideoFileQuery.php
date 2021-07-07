<?php

namespace App\Query\Account\TikTok;

use App\Entity\Social\TikTok\Video;
use App\Enum\UploadTypeEnum;
use App\Query\Render\CurrentRenderStatusForVideoQuery;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideoFileQuery
{
    public function __construct(
        private DatabaseFetcher                  $fetcher,
        private CurrentRenderStatusForVideoQuery $renderCheckQuery
    )
    {
    }

    public function execute(string $tiktokUrl): ?Video
    {
        $querieds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_tiktok_upload',
                'tu'
            )->join(
                'spinned_content_upload_status as us',
                'us.upload_id = tu.id AND tu.upload_type = "' . UploadTypeEnum::TIKTOK . '"'
            )->select(
                'tu.video_id'
            )->where(
                'us.remote_url = :tiktok_url AND tu.upload_type = "' . UploadTypeEnum::TIKTOK . '"'
            ),
            ['tiktok_url' => $tiktokUrl]
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
