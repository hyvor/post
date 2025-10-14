<?php

namespace App\Service\Integration\Relay\Response;

use App\Entity\Type\RelayDomainStatus;

class VerifyDomainResponse
{
    public string $domain;
    public RelayDomainStatus $status;
    public ?int $dkim_checked_at;
    public ?string $dkim_error_message;
}
