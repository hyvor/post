<?php

namespace App\Api\Console\Object;

use App\Entity\Domain;
use App\Entity\Type\RelayDomainStatus;

class DomainObject
{
    public int $id;
    public int $created_at;
    public string $domain;
    public string $dkim_public_key;
    public string $dkim_txt_name;
    public string $dkim_txt_value;

    public RelayDomainStatus $relay_status;
    public ?int $relay_last_checked_at;
    public ?string $relay_error_message;

    public function __construct(Domain $domainEntity)
    {
        $this->id = $domainEntity->getId();
        $this->created_at = $domainEntity->getCreatedAt()->getTimestamp();
        $this->domain = $domainEntity->getDomain();
        $this->dkim_public_key = $domainEntity->getDkimTxtvalue();
        $this->dkim_txt_name = $domainEntity->getDkimHost();
        $this->dkim_txt_value = $domainEntity->getDkimTxtvalue();
        $this->relay_status = $domainEntity->getRelayStatus();
        $this->relay_last_checked_at = $domainEntity->getRelayLastCheckedAt()?->getTimestamp() ?? null;
        $this->relay_error_message = $domainEntity->getRelayErrorMessage();
    }
}
