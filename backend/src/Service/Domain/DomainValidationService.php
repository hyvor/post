<?php

namespace App\Service\Domain;

use App\Entity\Newsletter;
use App\Repository\DomainRepository;
use App\Service\AppConfig;

class DomainValidationService
{
    public function __construct(
        private DomainRepository $domainRepository,
        private AppConfig $appConfig,
    ) {
    }

    /**
     * Validates if a domain is allowed to embed the signup form for a newsletter.
     *
     * @param Newsletter $newsletter
     * @param string|null $domain The domain from the Referer header
     * @return DomainValidationResult
     */
    public function validateDomain(Newsletter $newsletter, ?string $domain): DomainValidationResult
    {
        if ($domain === null || $domain === '') {
            return DomainValidationResult::rejected('Domain not provided');
        }

        // Always allow the app domain (for preview in Console)
        $appDomain = $this->extractDomain($this->appConfig->getUrlApp());
        if ($this->isDomainOrSubdomain($domain, $appDomain)) {
            return DomainValidationResult::allowed();
        }

        $allowedDomains = $newsletter->getAllowedDomains();

        // If allowed_domains is set, check against those domains
        if ($allowedDomains !== null && count($allowedDomains) > 0) {
            foreach ($allowedDomains as $allowedDomain) {
                if ($this->isDomainOrSubdomain($domain, $allowedDomain)) {
                    return DomainValidationResult::allowed();
                }
            }

            return DomainValidationResult::rejected(
                'This domain is not allowed to embed the signup form. ' .
                'Please add it to the allowed domains in your newsletter settings.'
            );
        }

        // If allowed_domains is null/empty, check against owner's active domains
        $ownerUserId = $newsletter->getUserId();
        if ($this->domainRepository->hasMatchingDomain($ownerUserId, $domain)) {
            return DomainValidationResult::allowed();
        }

        return DomainValidationResult::rejected(
            'This domain is not allowed to embed the signup form. ' .
            'Please add it to the allowed domains in your newsletter settings.'
        );
    }

    /**
     * Extracts the domain (host) from a URL.
     */
    public function extractDomainFromUrl(string $url): ?string
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? null;
    }

    /**
     * Extracts just the host from a URL string.
     */
    private function extractDomain(string $url): string
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? $url;
    }

    /**
     * Checks if $domain is equal to $allowedDomain or is a subdomain of it.
     *
     * For example, if $allowedDomain is "example.com":
     * - "example.com" returns true
     * - "sub.example.com" returns true
     * - "other.com" returns false
     */
    private function isDomainOrSubdomain(string $domain, string $allowedDomain): bool
    {
        $domain = strtolower(trim($domain));
        $allowedDomain = strtolower(trim($allowedDomain));
        if ($domain === $allowedDomain)
            return true;
        if (str_ends_with($domain, '.' . $allowedDomain))
            return true;
        return false;
    }
}
