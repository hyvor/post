<?php

namespace App\Api\Console\Object;

use App\Entity\SubscriberMetadataDefinition;
use App\Entity\Type\SubscriberMetadataDefinitionType;

class SubscriberMetadataDefinitionObject
{

    public int $id;
    public int $created_at;
    public string $key;
    public string $name;
    public SubscriberMetadataDefinitionType $type;

    public function __construct(SubscriberMetadataDefinition $definition)
    {
        $this->id = $definition->getId();
        $this->created_at = $definition->getCreatedAt()->getTimestamp();
        $this->key = $definition->getKey();
        $this->name = $definition->getName();
        $this->type = $definition->getType();
    }

}