<?php

namespace Tests\Case;


use Hyvor\Internal\Auth\AuthFake;

class AppTestCase extends \Illuminate\Foundation\Testing\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        AuthFake::enable(['id' => 1]);
    }
}
