<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AppConfig
{

    public function __construct(
        #[Autowire('%env(string:URL_APP)%')]
        private string $urlApp,
        #[Autowire('%env(string:URL_ARCHIVE)%')]
        private string $urlArchive,

        // Relay configuration
        #[Autowire('%env(string:RELAY_URL)%')]
        private string $relayUrl,

        #[Autowire('%env(string:RELAY_API_KEY)%')]
        private string $relayApiKey,

        // Email configuration
        #[Autowire('%env(int:MAX_EMAILS_PER_SECOND)%')]
        private int    $maxEmailsPerSecond,

        #[Autowire('%env(string:SYSTEM_MAIL_DOMAIN)%')]
        private string $systemMailDomain,

        #[Autowire('%env(string:SYSTEM_MAIL_REPLY_TO)%')]
        private string $systemMailReplyTo,
    )
    {
    }

    public function getUrlApp(): string
    {
        return $this->urlApp;
    }

    public function getUrlArchive(): string
    {
        return $this->urlArchive;
    }

    public function getMaxEmailsPerSecond(): int
    {
        return $this->maxEmailsPerSecond;
    }

    public function getSystemMailDomain(): string
    {
        return $this->systemMailDomain;
    }

    public function getRelayUrl(): string
    {
        return rtrim($this->relayUrl, '/');
    }

    public function getRelayApiKey(): string
    {
        return $this->relayApiKey;
    }

    public function getSystemMailReplyTo(): string
    {
        return $this->systemMailReplyTo;
    }

}
