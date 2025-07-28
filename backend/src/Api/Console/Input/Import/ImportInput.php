<?php

namespace App\Api\Console\Input\Import;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ImportInput
{
    /**
     * @var array<string, string|null>
     */
    #[Assert\Type('array')]
    public array $mapping;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (!\array_key_exists('email', $this->mapping) ||
                \trim((string) $this->mapping['email']) === ''
        ) {
            $context->buildViolation('The mapping must contain the key "email".')
                ->atPath('mapping')
                ->addViolation();
        }
    }
}
