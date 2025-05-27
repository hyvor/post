<?php

namespace App\Api\Console\Object;

use App\Entity\SendingProfile;

class SendingAddressObject
{
    public int $id;
    public string $email;
    public bool $is_default;
    public DomainObject $domain;

    public function __construct(SendingProfile $sendingAddress)
    {
        $this->id = $sendingAddress->getId();
        $this->email = $sendingAddress->getEmail();
        $this->is_default = $sendingAddress->isDefault();
        $this->domain = new DomainObject($sendingAddress->getDomain());
    }
}
