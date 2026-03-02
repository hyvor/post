<?php

namespace App\Api\Console\Authorization;

use Attribute;

// use for oraganization-level endpoints in the Console API (/console/usage) that
// is not project-specific
#[Attribute(Attribute::TARGET_METHOD)]
class OrganizationLevelEndpoint
{

}
