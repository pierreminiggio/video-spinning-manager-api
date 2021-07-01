<?php

namespace App\Entity\Account;

class APIAccount
{
    public function __construct(
        public string $url,
        public string $token
    )
    {
    }
}
