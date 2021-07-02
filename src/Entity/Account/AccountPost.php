<?php

namespace App\Entity\Account;

class AccountPost
{

    public function __construct(
        public int $accountId,
        public ?string $remoteUrl
    )
    {
    }
}
