<?php

namespace App\Entity\Account;

use DateTimeInterface;

class SocialMediaAccount
{
    public function __construct(
        public int $id,
        public string $username,
        public ?DateTimeInterface $lastPosted
    )
    {
    }
}