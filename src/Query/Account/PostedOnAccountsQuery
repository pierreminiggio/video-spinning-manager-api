<?php

namespace App\Query\Account;

use App\Entity\Account\AccountCollection;
use App\Entity\Account\SocialMediaAccount;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class PostedOnAccountsQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    /**
     * @param int[]
     */
    public function execute(int $videoId): array
    {
        $fetchedAccounts = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_tiktok_upload'
            )->select(
                'account_id'
            )->where(
                'video_id = :video_id'
            ),
            ['video_id' => $videoId]
        );

        return array_map(fn (array $fetchedAccount): int => (int) $fetchedAccount['account_id'], $fetchedAccounts);
    }
}
