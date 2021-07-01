<?php

namespace App\Query\Account\TikTok;

use App\Entity\Account\APIAccount;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class AccountQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(int $accountId): ?APIAccount
    {
        $fetchedTikTokAccounts = $this->fetcher->query(
            $this->fetcher->createQuery(
                'postable_tiktok_account',
            )->select(
                'api_url, token'
            )->where(
                'id = :id'
            ),
            ['id' => $accountId]
        );

        if (! $fetchedTikTokAccounts) {
            return null;
        }

        $fetchedTikTokAccount = $fetchedTikTokAccounts[0];

        return new APIAccount($fetchedTikTokAccount['api_url'], $fetchedTikTokAccount['token']);
    }
}