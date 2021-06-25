<?php

namespace App\Controller;

use App\Normalizer\NormalizerInterface;
use App\Query\Query;

class DetailController
{

    public function __construct(private Query $query, private NormalizerInterface $normalizer)
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
        echo json_encode($this->normalizer->normalize($entity));
    }
}
