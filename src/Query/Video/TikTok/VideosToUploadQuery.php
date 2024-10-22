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
    public function execute(bool $idsOnly = true): array
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

        $selectArgs = ['tu.id'];

        if (! $idsOnly) {
            $selectArgs[] = 'ta.tiktok_name';
            $selectArgs[] = 'tu.legend';
            $selectArgs[] = 'tu.publish_at';
        }

        $videoQuery = $this->fetcher->createQuery(
            'spinned_content_video',
            'v'
        )->join(
            'spinned_content_tiktok_upload as tu',
            'tu.video_id = v.id'
        )->join(
            '(' . $orderedUploadStatusQuery->build() . ') as cus',
            'cus.upload_id = tu.id'
        );

        if (! $idsOnly) {
            $videoQuery = $videoQuery->join(
                'postable_tiktok_account as pta',
                'tu.account_id = pta.id'
            )->join(
                'tiktok_account as ta',
                'pta.account_id = ta.id'
            );
        }

        $videoQuery = $videoQuery->select(
            ...$selectArgs
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
        );

        $queriedVideos = $this->fetcher->query($videoQuery);

        return array_map(function (array $queriedVideo) use ($idsOnly): int | array {
            if ($idsOnly) {
                return (int) $queriedVideo['id'];
            }

            return [
                'id' => $queriedVideo['id'],
                'tiktok_name' => $queriedVideo['tiktok_name'],
                'legend' => $queriedVideo['legend'],
                'publish_at' => $queriedVideo['publish_at'],
            ];
        }, $queriedVideos);
    }
}
