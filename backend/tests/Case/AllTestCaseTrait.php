<?php

namespace App\Tests\Case;

use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

trait AllTestCaseTrait
{
    use Factories;
    use InteractsWithMessenger;
}