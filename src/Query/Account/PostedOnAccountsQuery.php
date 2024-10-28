<?php

namespace App\Query\Account;

use App\Entity\Account\AccountPost;
use App\Query\Video\TikTok\CurrentUploadStatusForTikTokQuery;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class PostedOnAccountsQuery
{
    public function __construct(private DatabaseFetcher $fetcher, private CurrentUploadStatusForTikTokQuery $query)
    {
    }

    /**
     * @return AccountPost[]
     */
    public function execute(int $videoId): array
    {
        $fetchedTikToks = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_tiktok_upload'
            )->select(
                'id, account_id'
            )->where(
                'video_id = :video_id'
            ),
            ['video_id' => $videoId]
        );

        return array_map(function (array $fetchedTikTok): AccountPost {
            $tikTokId = (int) $fetchedTikTok['id'];
            $uploadStatus = $this->query->execute($tikTokId);

            return new AccountPost(
                (int) $fetchedTikTok['account_id'],
                $uploadStatus !== null && $uploadStatus->remoteUrl ? $uploadStatus->remoteUrl : null
            );
        }, $fetchedTikToks);
    }
}
