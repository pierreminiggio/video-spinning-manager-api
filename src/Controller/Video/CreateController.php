<?php

namespace App\Controller\Video;

use App\Command\Video\CreateCommand;
use App\Http\Request\JsonBodyParser;

class CreateController
{
    public function __construct(
        private JsonBodyParser $parser,
        private CreateCommand $command
    )
    {
    }

    public function __invoke(int $contentId, ?string $body): void
    {
        $jsonBody = $this->parser->parse($body);

        if (empty($jsonBody['name'])) {
            http_response_code(400);
            return;
        }

        $id = $this->command->execute($contentId, $jsonBody['name']);
        echo json_encode(['id' => $id]);
        http_response_code(200);
    }
}
