<?php

namespace App\Query\Account;

use App\Entity\Account\AccountCollection;
use App\Entity\Account\SocialMediaAccount;
use App\Query\Account\TikTok\PredictedNextPostTimeQuery;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class SocialMediaAccountsByContentQuery
{
    public function __construct(private DatabaseFetcher $fetcher, private PredictedNextPostTimeQuery $timeQuery)
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
            array_map(function (array $fetchedTikTokAccount): SocialMediaAccount {
                $tikTokAccountId = (int) $fetchedTikTokAccount['id'];
                return new SocialMediaAccount(
                    $tikTokAccountId,
                    $fetchedTikTokAccount['tiktok_name'],
                    $this->timeQuery->execute($tikTokAccountId)
                );
                },
            $fetchedTikTokAccounts
        ));
    }
}