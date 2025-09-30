<?php

namespace App\Api\Console\Input\Newsletter;

use App\Api\Console\Object\NewsletterObject;
use App\Service\Newsletter\Constraint\Subdomain;
use App\Util\OptionalPropertyTrait;

class UpdateNewsletterInput extends NewsletterObject
{
    use OptionalPropertyTrait;

    public string $name;

    #[Subdomain]
    public string $subdomain;

    public const UNUPDATABLE_PROPERTIES = [
        'id',
        'created_at',
    ];

    public function __construct()
    {}

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

    public function getSetProperties(): array
    {
        return $this->setProperties;
    }

}
