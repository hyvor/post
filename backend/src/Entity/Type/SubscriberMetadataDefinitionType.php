<?php

namespace App\Entity\Type;

enum SubscriberMetadataDefinitionType: string
{

    case TEXT = 'text';

    // Maybe in the future, we will add more types like select, checkbox, etc.
    
    public function toJsonType(): string
    {
        return match ($this) {
            SubscriberMetadataDefinitionType::TEXT => 'string',
        };
    }

}
