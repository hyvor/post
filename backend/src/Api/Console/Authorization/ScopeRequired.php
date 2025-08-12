<?php

namespace App\Api\Console\Authorization;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ScopeRequired
{
    public function __construct(public Scope $scope)
    {
    }
}
