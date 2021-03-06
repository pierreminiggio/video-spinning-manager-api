<?php

namespace App\Controller;

use App\Query\QueryWithIdParameter;
use App\Serializer\SerializerInterface;

class DetailController
{

    public function __construct(private QueryWithIdParameter $query, private SerializerInterface $serializer)
    {
    }

    public function __invoke(int $id): void
    {
        $entity = $this->query->execute($id);

        if ($entity === null) {
            http_response_code(404);

            return;
        }

        http_response_code(200);
        echo $this->serializer->serialize($entity);
    }
}
