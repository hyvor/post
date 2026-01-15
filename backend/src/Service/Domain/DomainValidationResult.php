<?php

namespace App\Service\Domain;

readonly class DomainValidationResult
{
    private function __construct(
        public bool $isAllowed,
        public ?string $message = null,
    ) {
    }

    public static function allowed(): self
    {
        return new self(true);
    }

    public static function rejected(string $message): self
    {
        return new self(false, $message);
    }
}
