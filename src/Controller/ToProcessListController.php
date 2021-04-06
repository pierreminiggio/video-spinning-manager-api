<?php

namespace App\Controller;

use App\Repository\ToProcessRepository;

class ToProcessListController
{

    public function __construct(private ToProcessRepository $repository)
    {
    }

    public function __invoke(): void
    {
        http_response_code(200);
        echo json_encode($this->repository->findAll());
    }
}
