<?php

namespace App\Query\Video\TikTok;

use App\Entity\Social\UploadStatus;
use App\Enum\UploadTypeEnum;
use DateTime;
use NeutronStars\Database\Query;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class CurrentUploadStatusForTiKTokQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(int $tikTokId): ?UploadStatus
    {
        $fetchedStatuses = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_upload_status'
            )->select(
                'id',
                'finished_at',
                'failed_at'
            )->where(
                'upload_id = :upload_id AND upload_type = "' . UploadTypeEnum::TIKTOK . '"'
            )->orderBy(
                'created_at',
                Query::ORDER_BY_DESC
            )->limit(1),
            ['upload_id' => $tikTokId]
        );

        if (! $fetchedStatuses) {
            return null;
        }

        $fetchedStatus = $fetchedStatuses[0];

        return new UploadStatus(
            (int) $fetchedStatus['id'],
            $fetchedStatus['finished_at'] ? new DateTime($fetchedStatus['finished_at']) : null,
            $fetchedStatus['failed_at'] ? new DateTime($fetchedStatus['failed_at']) : null
        );
    }
}