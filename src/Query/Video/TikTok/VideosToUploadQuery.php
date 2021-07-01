<?php

namespace App\Query\Video\TikTok;

use App\Enum\UploadTypeEnum;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideosToUploadQuery
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
        $orderedUploadStatusQuery = $this->fetcher->createQuery(
            $this->fetcher->createQuery(
                'spinned_content_upload_status'
            )->select(
                'upload_id',
                'max(id) as last_id'
            )->groupBy(
                'upload_id'
            )->where(
                'upload_type = "' . UploadTypeEnum::TIKTOK . '"'
            ),
            'lus'
        )->select(
            'lus.last_id as id',
            'lus.upload_id',
            'us.finished_at',
            'us.failed_at'
        )->join(
            'spinned_content_upload_status as us',
            'us.id = lus.last_id'
        )->where(
            'us.upload_type = "' . UploadTypeEnum::TIKTOK . '"'
        );

        $queriedVideos = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video',
                'v'
            )->join(
                'spinned_content_tiktok_upload as tu',
                'tu.video_id = v.id'
            )->join(
                '(' . $orderedUploadStatusQuery->build() . ') as cus',
                'cus.upload_id = tu.id'
            )->select(
                'tu.id'
            )->where(
                'v.finished_at IS NOT NULL
                AND (
                    (cus.finished_at IS NULL AND cus.failed_at IS NOT NULL)
                    OR cus.id IS NULL
                )
                AND tu.id IS NOT NULL
                AND tu.publish_at <= NOW()'
            )->orderBy(
                'tu.publish_at'
            )
        );

        return array_map(fn (array $queriedVideo) => (int) $queriedVideo['id'], $queriedVideos);
    }
}