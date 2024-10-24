<?php

namespace App\Controller\Social;

use App\Query\Video\TikTok\VideosToUploadQuery;

class TuUploadListController
{
    public function __construct(private string $apiUrl, private VideosToUploadQuery $query)
    {
    }

    public function __invoke(): void
    {
        http_response_code(200);
        $videos = $this->query->execute(false);

        $apiUrl = $this->apiUrl;

        $videos = array_map(function (array $video) use ($apiUrl): array {
            $video['fileUrl'] = $apiUrl . '/render/' . $video['fileUrl'];

            return $video;
        }, $videos);

        echo json_encode($videos);
    }
}
