<?php

namespace App\Query;

interface Query
{
    public function execute(int $id): mixed;
}
