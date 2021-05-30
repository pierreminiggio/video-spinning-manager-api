<?php

namespace App\Controller;

use App\Query\ToProcessDetailQuery;

class ToProcessDetailController
{

    public function __construct(private ToProcessDetailQuery $query)
    {
    }

    public function __invoke(int $id): void
    {
        $entity = $this->query->findById($id);

        if ($entity === null) {
            http_response_code(404);

            return;
        }

        http_response_code(200);
        echo json_encode($entity);
    }
}
