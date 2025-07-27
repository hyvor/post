<?php

namespace App\Api\Console\Object;

use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberImportStatus;

class SubscriberImportObject
{
    public int $id;
    public int $created_at;
    public SubscriberImportStatus $status;
    /** @var array<string, string | null> | null */
    public ?array $fields = null;
    public ?string $error_message = null;


    public function __construct(SubscriberImport $import)
    {
        $this->id = $import->getId();
        $this->created_at = $import->getCreatedAt()->getTimestamp();
        $this->status = $import->getStatus();
        $this->fields = $import->getFields();
        $this->error_message = $import->getErrorMessage();
    }
}
