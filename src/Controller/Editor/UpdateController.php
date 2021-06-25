<?php

namespace App\Controller\Editor;

use App\Command\Editor\UpdateCommand;
use App\Http\Request\JsonBodyParser;

class UpdateController
{
    public function __construct(
        private JsonBodyParser $parser,
        private UpdateCommand $command
    )
    {
    }

    public function __invoke(int $videoId, ?string $body): void
    {
        $jsonBody = $this->parser->parse($body);

        if (
            ! isset($jsonBody['clips'])
            || ! isset($jsonBody['texts'])
            || ! isset($jsonBody['clipMakerProps'])
        ) {
            http_response_code(400);
            return;
        }
        
        $this->command->execute(
            $videoId,
            $jsonBody['clips'],
            $jsonBody['texts'],
            $jsonBody['clipMakerProps']
        );
        
        http_response_code(204);
    }
}
