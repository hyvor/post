<?php

namespace App\Service\Integration\Relay\Response;

class VerifyDomainResponse
{
    public string $domain;
    public bool $dkim_verified;
    public ?int $dkim_checked_at;
    public ?string $dkim_error_message;
}
