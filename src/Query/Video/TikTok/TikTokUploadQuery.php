<?php

namespace App\Query\Video\TikTok;

use App\Entity\Video\TikTok\TikTokUpload;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class TikTokUploadQuery
{
    public function __construct(
        private DatabaseFetcher $fetcher
    )
    {
    }

    public function execute(int $tiktokId): ?TikTokUpload
    {
        $queriedTikToks = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_tiktok_upload',
            )->select(
                'id',
                'video_id',
                'account_id',
                'legend'
            )->where(
                'id = :id'
            )->limit(1),
            ['id' => $tiktokId]
        );

        if (! $queriedTikToks) {
            return null;
        }

        $queriedTikTok = $queriedTikToks[0];

        return new TikTokUpload(
            (int) $queriedTikTok['id'],
            (int) $queriedTikTok['video_id'],
            (int) $queriedTikTok['account_id'],
            $queriedTikTok['legend']
        );
    }
}
