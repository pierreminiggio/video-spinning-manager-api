<?php

namespace App\Query\Account\TikTok;

use DateTime;
use DateTimeInterface;
use NeutronStars\Database\Query;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class PredictedNextPostTimeQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(int $accountId): ?DateTimeInterface
    {
        $dates = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_tiktok_upload'
            )->select(
                'publish_at'
            )->where(
                'account_id = :account_id'
            )->orderBy(
                'publish_at',
                Query::ORDER_BY_DESC
            )->limit(
                2
            ),
            ['account_id' => $accountId]
        );

        if (count($dates) < 2) {
            return null;
        }

        $latestDate = new DateTime($dates[0]);
        $earliestDate = new DateTime($dates[1]);
        $dateInterval = $latestDate->diff($earliestDate);

        $nextPredictedDate = clone $latestDate;
        $nextPredictedDate->add($dateInterval);

        return $nextPredictedDate;
    }
}