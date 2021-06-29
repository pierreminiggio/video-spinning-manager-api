<?php

namespace App\Entity\Account;

class AccountCollection
{

    /**
     * @param SocialMediaAccount[] $tiktok
     */
    public function __construct(
        public array $tiktok
    )
    {
    }
}