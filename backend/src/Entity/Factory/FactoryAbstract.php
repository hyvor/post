<?php

namespace App\Entity\Factory;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

/**
 * @template T of Entity
 */
abstract class FactoryAbstract
{

    protected \Faker\Generator $fake;

    public function __construct(protected ObjectManager $manager)
    {
        $this->fake = Factory::create();
    }

    /**
     * @return T
     */
    abstract public function define();

    /**
     * @param ?callable(T, int): void $callback
     */
    public function createMany(int $count, ?callable $callback = null): array
    {
        $entities = [];
        for ($i = 0; $i < $count; $i++) {
            $entity = $this->define();
            if ($callback) {
                $callback($entity, $i);
            }
            $entities[] = $entity;
            $this->manager->persist($entity);
        }
        $this->manager->flush();
        return $entities;
    }

    /**
     * @return T
     */
    public function create(?callable $callback = null)
    {
        $entities = $this->createMany(1, $callback);
        return $entities[0];
    }

}