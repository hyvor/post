<?php

namespace App\Api\Console\Normalizer;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::TARGET_PROPERTY)]
class NormalizeEmptyToNull
{
    public function __construct(public bool $trim = true)
    {
    }
}