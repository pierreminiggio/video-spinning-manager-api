<?php

namespace App\Controller\Editor\Text\Preset;

use App\Query\Editor\Preset\ListQuery;
use App\Serializer\SerializerInterface;

class ListController
{

    public function __construct(private ListQuery $query, private SerializerInterface $serializer)
    {
    }

    public function __invoke(): void
    {
        $presets = $this->query->execute();

        http_response_code(200);
        echo $this->serializer->serialize($presets);
    }
}