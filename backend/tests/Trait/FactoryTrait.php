<?php

namespace App\Tests\Trait;

use App\Entity\Factory\FactoryAbstract;
use Doctrine\Persistence\ObjectManager;

trait FactoryTrait
{

    /**
     * @param class-string<FactoryAbstract> $factory
     * @return void
     */
    public function factory(string $factory)
    {
        $container = static::getContainer();
        $manager = $container->get(ObjectManager::class);
        return new $factory($manager);
    }

}