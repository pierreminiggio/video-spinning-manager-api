<?php

namespace App\Http\Request;

class JsonBodyParser
{
    public function parse(?string $bodyString): array
    {
        if (! $bodyString) {
            http_response_code(400);
            exit;
        }

        $jsonBody = json_decode($bodyString, true);

        if (! $jsonBody) {
            http_response_code(400);
            exit;
        }

        return $jsonBody;
    }
}
