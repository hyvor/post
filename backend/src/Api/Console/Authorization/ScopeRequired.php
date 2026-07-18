<?php

namespace App\Api\Console\Authorization;

use Attribute;
use Hyvor\Internal\CloudApi\Scope\PostScope;

#[Attribute(Attribute::TARGET_METHOD)]
class ScopeRequired
{
    public function __construct(public PostScope $scope)
    {
    }
}
