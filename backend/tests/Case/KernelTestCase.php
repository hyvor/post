<?php

namespace App\Tests\Case;

use App\Tests\Trait\FactoryTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{

    protected Container $container;
    protected EntityManagerInterface $em;

    use FactoryTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);
        $this->em = $em;
    }

}
