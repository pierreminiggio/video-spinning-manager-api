<?php

namespace App\Query\Account\TikTok;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class CanVideoBePostedOnThisTikTokAccountQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(int $videoId, int $tiktokAccountId): bool
    {
        $fetchedTikTokAccounts = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video',
                'scv'
            )->join(
                'spinned_content as sc',
                'sc.id = scv.content_id'
            )->join(
                'postable_tiktok_account_spinned_youtube_account as ptkasya',
                'ptkasya.youtube_id = sc.account_id'
            )->select(
                'scv.id'
            )->where(
                'scv.id = :video_id AND ptkasya.tiktok_id = :tiktok_id'
            ),
            ['video_id' => $videoId, 'tiktok_id' => $tiktokAccountId]
        );

        return ! empty($fetchedTikTokAccounts);
    }
}