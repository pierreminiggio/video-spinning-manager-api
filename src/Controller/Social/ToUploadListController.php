<?php

namespace App\Controller\Social;

use App\Query\Video\TikTok\VideosToUploadQuery;

class ToUploadListController
{
    public function __construct(private string $apiUrl, private VideosToUploadQuery $query)
    {
    }

    public function __invoke(): void
    {
        $videos = $this->query->execute(false);

        $apiUrl = $this->apiUrl;

        $videos = array_map(function (array $video) use ($apiUrl): array {
            $video['fileUrl'] = $apiUrl . '/render/' . $video['fileUrl'];

            return $video;
        }, $videos);

        http_response_code(200);
        echo json_encode($videos);
    }
}
