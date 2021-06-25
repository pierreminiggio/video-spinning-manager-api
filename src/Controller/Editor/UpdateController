<?php

namespace App\Controller\Editor;

use App\Http\Request\JsonBodyParser;

class UpdateController
{
    public function __construct(
        private JsonBodyParser $parser
    )
    {
    }

    public function __invoke(int $contentId, ?string $body): void
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

        var_dump($jsonBody); die;
/*
        $id = $this->command->execute(
            $contentId,
            $jsonBody['name'],
            $jsonBody['width'],
            $jsonBody['height']
        );*/
        http_response_code(204);
    }
}
