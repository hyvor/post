<?php

namespace App\Api\Console\Object;

use App\Entity\SubscriberImport;

class SubscriberImportFieldObject
{
    public int $import_id;
    /**
     * @var string[]
     */
    public array $fields;


    /**
     * @param string[] $fields
     */
    public function __construct(SubscriberImport $import, array $fields)
    {
        $this->import_id = $import->getId();
        $this->fields = $fields;
    }
}
