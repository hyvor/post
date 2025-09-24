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

    public function __construct(Domain $domain)
    {
        $this->id = $domain->getId();
        $this->created_at = $domain->getCreatedAt()->getTimestamp();
        $this->domain = $domain->getDomain();
        $this->dkim_public_key = $domain->getDkimTxtvalue();
        $this->dkim_txt_name = $domain->getDkimHost();
        $this->dkim_txt_value = $domain->getDkimTxtvalue();
        $this->relay_status = $domain->getRelayStatus();
        $this->relay_last_checked_at = $domain->getRelayLastCheckedAt()?->getTimestamp() ?? null;
        $this->relay_error_message = $domain->getRelayErrorMessage();
    }
}
