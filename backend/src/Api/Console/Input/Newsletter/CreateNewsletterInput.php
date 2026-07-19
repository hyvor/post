<?php

namespace App\Api\Console\Input\Newsletter;

use App\Service\Newsletter\Constraint\Subdomain;
use Symfony\Component\Validator\Constraints as Assert;

class CreateNewsletterInput
{

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 50)]
    #[Subdomain]
    public string $subdomain;

    public bool $autogenerate_subdomain_on_duplicate = false;

    /**
     * @var array<string, string>
     */
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Length(max: 255),
    ])]
    public array $metadata = [];

}
