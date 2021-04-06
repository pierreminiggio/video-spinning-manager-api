<?php

namespace App\Controller;

use App\Command\EndProcessCommand;

class EndProcessController
{
    public function __construct(private EndProcessCommand $command)
    {
    }

    public function __invoke(int $id): void
    {
        $this->command->markAsDone($id);
        http_response_code(204);
    }
}
