<?php

namespace App\Api\Console\Input\Import;

use Symfony\Component\Validator\Constraints as Assert;

class ImportInput
{
    /**
     * @var array<string, string|null>
     */
    #[Assert\Type('array')]
    #[Assert\Count(min: 1, minMessage: 'At least the mapping for email should be provided.')]
    public array $mapping;
}
