<?php

namespace App\Util;

class ClassUpdater
{
    public static function updateIfExists(object $source, object $target, string $property): void
    {
        if ($source->hasProperty($property)) {
            $target->$property = $source->$property;
        }
    }
}
