<?php

namespace App\Query;

interface QueryWithIdParameter
{
    public function execute(int $id): mixed;
}
