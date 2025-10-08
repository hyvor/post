<?php

namespace App\Api\Console\Input\Newsletter;

use App\Service\Newsletter\Constraint\Subdomain;
use Symfony\Component\Validator\Constraints as Assert;

class SubdomainAvailabilityInput
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50)]
    #[Subdomain]
    public string $subdomain;
}
