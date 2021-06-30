<?php

namespace App\Controller\Social\TikTok;

use App\Command\Social\TikTok\PostCommand;
use App\Query\Account\TikTok\CanVideoBePostedOnThisTikTokAccountQuery;
use DateTime;

class PostController
{

    public function __construct(
        private CanVideoBePostedOnThisTikTokAccountQuery $canVideoBePostedOnThisTikTokAccountQuery,
        private PostCommand $command
    )
    {
    }

    public function __invoke(int $videoId, ?string $body): void
    {
        if (! $body) {
            http_response_code(400);

            return;
        }

        $jsonBody = json_decode($body, true);

        if (! $jsonBody) {
            http_response_code(400);

            return;
        }

        if (
            ! isset($jsonBody['accountId'])
            || ! isset($jsonBody['legend'])
            || ! isset($jsonBody['publishAt'])
        ) {
            http_response_code(400);

            return;
        }

        $tikTokAccountId = $jsonBody['accountId'];

        $canVideoBePostedOnThisTikTokAccount = $this->canVideoBePostedOnThisTikTokAccountQuery->execute(
            $videoId,
            $tikTokAccountId
        );

        if (! $canVideoBePostedOnThisTikTokAccount) {
            http_response_code(401);

            return;
        }

        $this->command->execute(
            $videoId,
            $tikTokAccountId,
            $jsonBody['legend'],
            new DateTime($jsonBody['publishAt'])
        );

        http_response_code(204);
    }
}
