<?php

namespace App\Controller\Social\TikTok;

use App\Query\Account\TikTok\VideoFileQuery;
use App\Serializer\SerializerInterface;

class VideoFileController
{

    public function __construct(private VideoFileQuery $query, private SerializerInterface $serializer)
    {
    }

    public function __invoke(?string $tikTokUrl): void
    {
        if (! $tikTokUrl) {
            http_response_code(400);

            return;
        }
      
        $entity = $this->query->execute($tikTokUrl);

        if ($entity === null) {
            http_response_code(404);

            return;
        }

        http_response_code(200);
        echo $this->serializer->serialize($entity);
    }
}
