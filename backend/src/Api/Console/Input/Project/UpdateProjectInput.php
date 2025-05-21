<?php

namespace App\Api\Console\Input\Project;

use App\Api\Console\Object\ProjectObject;
use App\Util\OptionalPropertyTrait;

class UpdateProjectInput extends ProjectObject
{
    use OptionalPropertyTrait;

    public string $name;
    public string $default_email_username;

    public const UNUPDATABLE_PROPERTIES = [
        'id',
        'created_at',
    ];

    public function __construct()
    {}

    /**
     * @return array<string>
     */
    public function getSetProperties(): array
    {
        $properties = [];
        foreach (get_object_vars($this) as $property => $value) {
            if ($this->hasProperty($property) && !in_array($property, self::UNUPDATABLE_PROPERTIES)) {
                $properties[] = $property;
            }
        }
        return $properties;
    }
}
