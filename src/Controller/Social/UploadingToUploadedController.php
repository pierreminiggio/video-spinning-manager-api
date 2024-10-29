<?php

namespace App\Controller\Social;

use App\Command\Social\MarkAsFinishedCommand;
use App\Enum\UploadTypeEnum;
use App\Http\Request\JsonBodyParser;
use App\Query\Video\TikTok\CurrentUploadStatusForTikTokQuery;
use App\Query\Video\TikTok\TikTokUploadQuery;

class UploadingToUploadedController
{

    public function __construct(
        private JsonBodyParser $parser,
        private TikTokUploadQuery $tikTokUploadQuery,
        private CurrentUploadStatusForTikTokQuery $tikTokUploadStatusQuery,
        private MarkAsFinishedCommand $markAsFinishedCommand
    )
    {   
    }

    public function __invoke(int $id, ?string $body): void
    {
        $jsonBody = $this->parser->parse($body);

        if (empty($jsonBody['remoteUrl'])) {
            http_response_code(400);

            return;
        }

        $remoteUrl = $jsonBody['remoteUrl'];

        $tiktokUpload = $this->tikTokUploadQuery->execute($id);

        if (! $tiktokUpload) {
            http_response_code(404);

            return;
        }

        $uploadStatus = $this->tikTokUploadStatusQuery->execute($id);

        $isIndeedUploading = $uploadStatus !== null && $uploadStatus->failedAt === null && $uploadStatus
        ->finishedAt === null;
        if (! $isIndeedUploading) {
            http_response_code(409);
            echo json_encode(['message' => 'Video is not uploading']);

            return;
        }

        $this->markAsFinishedCommand->execute(UploadTypeEnum::TIKTOK, $id, $remoteUrl);

        http_response_code(201);
    }
}
