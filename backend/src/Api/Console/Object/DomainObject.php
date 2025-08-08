<?php

namespace App\Api\Console\Object;

use App\Entity\Domain;
use App\Service\Domain\DomainService;
use App\Service\Integration\Aws\AwsDomainService;

class DomainObject
{
    public int $id;
    public int $created_at;
    public string $domain;
    public string $dkim_public_key;
    public string $dkim_txt_name;
    public string $dkim_txt_value;

    /**
     * Whether the domain has been verified to be used with the current website ID
     */
    public bool $verified;

    /**
     * Whether the domain has been verified to be used with SES
     */
    public bool $verified_in_ses;

    public bool $requested_by_current_website;

    public function __construct(Domain $domain)
    {
        $this->id = $domain->getId();
        $this->created_at = $domain->getCreatedAt()->getTimestamp();
        $this->domain = $domain->getDomain();
        $this->dkim_public_key = $domain->getDkimTxtvalue();
        $this->dkim_txt_name = $domain->getDkimHost();
        $this->dkim_txt_value = $domain->getDkimTxtvalue();
        $this->verified_in_ses = $domain->isVerifiedInRelay();
    }
}
