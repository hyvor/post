<?php

namespace App\Service\Newsletter\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use function PHPUnit\Framework\assertIsString;

class SubdomainValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint): void
    {
        assert($constraint instanceof Subdomain);

        if ($value === null) {
            return; // skip empty values
        }

        assert(is_string($value));

        // Check length
        if (strlen($value) < 1 || strlen($value) > 63) {
            $this->context->buildViolation('Subdomain must be between 1 and 63 characters long.')
                ->addViolation();
            return;
        }

        // Must start and end with letter or digit
        if (!preg_match('/^[A-Za-z0-9]/', $value) || !preg_match('/[A-Za-z0-9]$/', $value)) {
            $this->context->buildViolation('Subdomain must start and end with a letter or digit.')
                ->addViolation();
            return;
        }

        // Only letters, digits, hyphens allowed
        if (!preg_match('/^[A-Za-z0-9-]+$/', $value)) {
            $this->context->buildViolation('Subdomain can only contain letters, digits, and hyphens.')
                ->addViolation();
            return;
        }

        // Disallow consecutive hyphens if the flag is false
        if (str_contains($value, '--')) {
            $this->context->buildViolation('Subdomain cannot contain consecutive hyphens.')
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }

}
