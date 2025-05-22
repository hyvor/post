<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AppConfig
{

    public function __construct(
        #[Autowire('%env(int:MAX_EMAILS_PER_SECOND)%')]
        private int $maxEmailsPerSecond,

        #[Autowire('%env(string:DEFAULT_EMAIL_DOMAIN)%')]
        private string $defaultEmailDomain,
    ) {
    }

    public function getMaxEmailsPerSecond(): int
    {
        return $this->maxEmailsPerSecond;
    }

    public function getDefaultEmailDomain(): string
    {
        return $this->defaultEmailDomain;
    }

}