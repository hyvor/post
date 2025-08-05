<?php

namespace App\Tests\Case;

use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\DependencyInjection\Container;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{

    use AllTestCaseTrait;

    protected Container $container;
    protected EntityManagerInterface $em;
    protected Encryption $encryption;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $this->container->get(EntityManagerInterface::class);
        $this->em = $em;

        $encryption = $this->container->get(Encryption::class);
        $this->assertInstanceOf(Encryption::class, $encryption);
        $this->encryption = $encryption;
    }

}
