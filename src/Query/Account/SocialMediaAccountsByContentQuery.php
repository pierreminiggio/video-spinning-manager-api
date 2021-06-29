<?php

namespace App\Query\Account;

use App\Entity\Account\AccountCollection;
use App\Entity\Account\SocialMediaAccount;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class SocialMediaAccountsByContentQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(int $contentId): AccountCollection
    {
        $fetchedTikTokAccounts = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content',
                'sc'
            )->join(
                'postable_tiktok_account_spinned_youtube_account as ptasya',
                'ptasya.youtube_id = sc.account_id'
            )->join(
                'postable_tiktok_account as pta',
                'pta.id = ptasya.tiktok_id'
            )->join(
                'tiktok_account as ta',
                'ta.id = pta.account_id'
            )->select(
                'pta.id, ta.tiktok_name'
            )->where(
                'sc.id = :content_id'
            ),
            ['content_id' => $contentId]
        );

        return new AccountCollection(
            array_map(fn (array $fetchedTikTokAccount): SocialMediaAccount => new SocialMediaAccount(
                (int) $fetchedTikTokAccount['id'],
                $fetchedTikTokAccount['tiktok_name'],
                null
            ),
            $fetchedTikTokAccounts
        ));
    }
}