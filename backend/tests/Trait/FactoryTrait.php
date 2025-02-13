<?php

namespace App\Tests\Trait;

use App\Entity\Factory\FactoryAbstract;
use Doctrine\ORM\EntityManagerInterface;
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
        $manager = $container->get(EntityManagerInterface::class);
        return new $factory($manager);
    }

}
