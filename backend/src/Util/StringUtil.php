<?php

namespace App\Util;

class StringUtil
{
    /**
     * Converts a snake_case string to camelCase
     */
    public static function snakeToCamelCase(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }
} 