<?php

namespace App\Api\Console\Input\Newsletter;

use App\Api\Console\Object\NewsletterObject;
use App\Service\Newsletter\Constraint\Subdomain;
use App\Util\OptionalPropertyTrait;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use OpenApi\Attributes as OA;

#[OA\Schema(required: ['name'])]
class UpdateNewsletterInput extends NewsletterObject
{
    use OptionalPropertyTrait;

    public string $name;

    #[Subdomain]
    public string $subdomain;

    /**
     * @var string[]
     */
    #[Ignore]
    private array $setProperties = [];

    public const UNUPDATABLE_PROPERTIES = [
        'id',
        'created_at',
    ];

    public function __construct() {}

    public function set(string $property, mixed $value): void
    {
        assert(
            property_exists($this, $property),
            "Property $property does not exist in " . __CLASS__,
        );
        $this->$property = $value;
        $this->setProperties[] = $property;
    }

    public function isSet(string $property): bool
    {
        return in_array($property, $this->setProperties);
    }

    /**
     * @return string[]
     */
    public function getSetProperties(): array
    {
        return $this->setProperties;
    }

}
