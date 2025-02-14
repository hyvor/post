<?php

namespace App\Tests\Trait;

use App\Entity\Factory\FactoryAbstract;
use Doctrine\ORM\EntityManagerInterface;

trait FactoryTrait
{

    /**
     * @template T of FactoryAbstract
     * @param class-string<T> $factory
     * @return T
     */
    public function factory(string $factory)
    {
        $container = static::getContainer();
        $manager = $container->get(EntityManagerInterface::class);
        return new $factory($manager);
    }

}
