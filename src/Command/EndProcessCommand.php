<?php

namespace App\Command;

use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class EndProcessCommand
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    public function markAsDone(int $id): void
    {
        $this->fetcher->exec(
            $this->fetcher
                ->createQuery('spinned_content')
                ->update('spinned = 1')
                ->where('id = :id')
            ,
            ['id' => $id]
        );
    }
}
