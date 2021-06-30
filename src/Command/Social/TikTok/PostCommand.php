<?php

namespace App\Command\Social\TikTok;

use DateTimeInterface;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class PostCommand
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(int $videoId, int $tikTokAccountId, string $legend, DateTimeInterface $publishAt): void
    {
        $alreadyPosted = ! empty($this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_tiktok_upload'
            )->select('id')->where(
                'video_id = :video_id AND account_id = :account_id'
            ),
            ['video_id' => $videoId, 'account_id' => $tikTokAccountId]
        ));

        if ($alreadyPosted) {
            return;
        }

        $this->fetcher->exec(
            $this->fetcher->createQuery(
                'spinned_content_tiktok_upload'
            )->insertInto(
                'video_id, account_id, legend, publish_at',
                ':video_id, :account_id, :legend, :publish_at'
            ),
            [
                'video_id' => $videoId,
                'account_id' => $tikTokAccountId,
                'legend' => $legend,
                'publish_at' => $publishAt->format('Y-m-d H:i:s')
            ]
        );
    }
}