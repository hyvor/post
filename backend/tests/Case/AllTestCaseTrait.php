<?php

namespace App\Tests\Case;

use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

trait AllTestCaseTrait
{

    use Factories;
    use InteractsWithMessenger;

    protected function getMessageBus(): MessageBusInterface
    {
        /** @var MessageBusInterface $bus */
        $bus = $this->container->get('messenger.default_bus');
        return $bus;
    }

}