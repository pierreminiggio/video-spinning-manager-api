<?php

namespace App\Query\Editor\Preset;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class ListQuery
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function execute(): array
    {
        $queriedEntities = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_text_preset as sc'
            )->select(
                'name, content'
            )->orderBy(
                'name'
            )
        );

        return array_map(fn (array $queriedEntity): array => [
            'name' => $queriedEntity['name'],
            'content' => json_decode($queriedEntity['content'])
        ], $queriedEntities);
    }
}