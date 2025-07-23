<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AppConfig
{

    public function __construct(
        #[Autowire('%env(int:MAX_EMAILS_PER_SECOND)%')]
        private int $maxEmailsPerSecond,

        #[Autowire('%env(string:DEFAULT_EMAIL_DOMAIN)%')]
        private string $defaultEmailDomain,

        /**
         * AWS SES Configuration
         * We use SES for sending emails.
         */
        #[Autowire('%env(string:SES_REGION)%')]
        private string $awsSesRegion,
        #[Autowire('%env(string:SES_ACCESS_KEY_ID)%')]
        private string $awsSesAccessKeyId,
        #[Autowire('%env(string:SES_SECRET_ACCESS_KEY)%')]
        private string $awsSesSecretAccessKey,
        #[Autowire('%env(string:SES_NEWSLETTER_CONFIGURATION_SET_NAME)%')]
        private string $awsSesNewsletterConfigurationSetName,

        /**
         * Hyvor Relay configuration
         */
        #[Autowire('%env(string:RELAY_URL)%')]
        private string $relayUrl,

        #[Autowire('%env(string:RELAY_API_KEY)%')]
        private string $relayApiKey,
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

    public function getAwsSesRegion(): string
    {
        return $this->awsSesRegion;
    }

    public function getAwsSesAccessKeyId(): string
    {
        return $this->awsSesAccessKeyId;
    }

    public function getAwsSesSecretAccessKey(): string
    {
        return $this->awsSesSecretAccessKey;
    }

    public function getAwsSesNewsletterConfigurationSetName(): string
    {
        return $this->awsSesNewsletterConfigurationSetName;
    }

    public function getRelayUrl(): string
    {
        return rtrim($this->relayUrl, '/');
    }

    public function getRelayApiKey(): string
    {
        return $this->relayApiKey;
    }

}