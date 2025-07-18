<?php

namespace App\Service\Import\Dto;

use App\Entity\Type\SubscriberImportStatus;
use App\Util\OptionalPropertyTrait;

class UpdateSubscriberImportDto
{
    use OptionalPropertyTrait;

    public SubscriberImportStatus $status;

    /**
     * @var array<string, string|null>
     */
    public ?array $fields;

    public ?string $errorMessage;
}
