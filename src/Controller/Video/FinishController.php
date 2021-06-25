<?php

namespace App\Controller\Video;

use App\Command\Video\FinishCommand;
use DateTime;

class FinishController
{
    public function __construct(private FinishCommand $command)
    {
    }

    public function __invoke(int $videoId): void
    {
        $this->command->execute($videoId, new DateTime());
        http_response_code(201);
    }
}