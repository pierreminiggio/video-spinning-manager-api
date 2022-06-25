<?php

namespace App\Controller;

use App\Query\Content\ToProcessListQuery;

class ToProcessListController
{

    public function __construct(private ToProcessListQuery $query)
    {
    }

    public function __invoke(): void
    {
        http_response_code(200);
        echo json_encode($this->query->findAll());
    }
}
