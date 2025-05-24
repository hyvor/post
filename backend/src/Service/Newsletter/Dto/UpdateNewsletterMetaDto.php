<?php

namespace App\Service\Newsletter\Dto;

use App\Entity\Meta\NewsletterMeta;

/**
 * extends NewsletterMeta
 * call ->set() to set a property
 * in NewsletterService::updateNewsletterMeta() we check if the property is set with ->isSet()
 */
class UpdateNewsletterMetaDto extends NewsletterMeta
{

    /**
     * @var string[]
     */
    private array $setProperties = [];

    public function set(string $property, mixed $value): void
    {
        assert(
            property_exists($this, $property),
            "Property $property does not exist in " . __CLASS__
        );
        $this->$property = $value;
        $this->setProperties[] = $property;
    }

    public function isSet(string $property): bool
    {
        return in_array($property, $this->setProperties);
    }

}
