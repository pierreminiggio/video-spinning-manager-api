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

        if (
            empty($jsonBody['name'])
            || empty($jsonBody['width'])
            || empty($jsonBody['height'])
            || empty($jsonBody['fps'])
        ) {
            http_response_code(400);
            return;
        }

        $id = $this->command->execute(
            $contentId,
            $jsonBody['name'],
            $jsonBody['width'],
            $jsonBody['height'],
            $jsonBody['fps']
        );
        echo json_encode(['id' => $id]);
        http_response_code(200);
    }
}
