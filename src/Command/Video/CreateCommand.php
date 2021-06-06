<?php

namespace App\Command\Video;

use Exception;
use NeutronStars\Database\Query;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class CreateCommand
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    /**
     * @throws Exception
     */
    public function execute(
        int $contentId,
        string $name,
        int $width,
        int $height
    ): int
    {
        $this->fetcher->exec(
            $this->fetcher->createQuery(
                'spinned_content_video'
            )->insertInto(
                'name,content_id,width,height',
                ':name,:content_id,:width,:height'
            ),
            [
                'content_id' => $contentId,
                'name' => $name,
                'width' => $width,
                'height' => $height
            ]
        );

        $fetchedIds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video'
            )->select(
                'id',
            )->where(
                'content_id = :content_id'
            )->andWhere(
                'name = :name'
            )->orderBy(
                'id',
                Query::ORDER_BY_DESC
            )->limit(1),
            [
                'content_id' => $contentId,
                'name' => $name
            ]
        );

        if (! $fetchedIds) {
            throw new Exception('Insert failed ?');
        }

        return (int) $fetchedIds[0]['id'];
    }
}
