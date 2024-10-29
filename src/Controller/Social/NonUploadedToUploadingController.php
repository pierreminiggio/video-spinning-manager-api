<?php

namespace App\Controller\Social;

use App\Command\Social\MarkAsUploadingCommand;
use App\Enum\UploadTypeEnum;
use App\Query\Video\TikTok\CurrentUploadStatusForTikTokQuery;
use App\Query\Video\TikTok\TikTokUploadQuery;

class NonUploadedToUploadingController
{

    public function __construct(
        private TikTokUploadQuery $tikTokUploadQuery,
        private CurrentUploadStatusForTikTokQuery $tikTokUploadStatusQuery,
        private MarkAsUploadingCommand $markAsUploadingCommand
    )
    {   
    }

    public function __invoke(int $id): void
    {
        $tiktokUpload = $this->tikTokUploadQuery->execute($id);

        if (! $tiktokUpload) {
            http_response_code(404);

            return;
        }

        $uploadStatus = $this->tikTokUploadStatusQuery->execute($id);

        $isAlreadyUploading = $uploadStatus !== null && $uploadStatus->failedAt === null;
        if ($isAlreadyUploading) {
            http_response_code(409);
            echo json_encode(['message' => 'Video already is uploading']);

            return;
        }
        
        $this->markAsUploadingCommand->execute(UploadTypeEnum::TIKTOK, $id);

        http_response_code(201);
    }
}
