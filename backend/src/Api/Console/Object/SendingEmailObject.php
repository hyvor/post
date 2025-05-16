<?php

namespace App\Api\Console\Object;

use App\Entity\SendingEmail;

class SendingEmailObject
{
    public int $id;
    public string $email;
    public DomainObject $domain;

    public function __construct(SendingEmail $sendingEmail)
    {
        $this->id = $sendingEmail->getId();
        $this->email = $sendingEmail->getEmail();
        $this->domain = new DomainObject($sendingEmail->getCustomDomain());
    }
}
