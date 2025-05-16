<?php

namespace App\Tests\Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Object\DomainObject;
use App\Entity\Domain;
use App\Service\Domain\DomainService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
#[CoversClass(DomainObject::class)]
#[CoversClass(Domain::class)]
class GetDomainsTest extends WebTestCase
{
    // TODO: tests for authentication

    public function testListDomainsEmpty(): void
    {
        $response = $this->consoleApi(
            null,
            'GET',
            '/domains'
        );

        $this->assertSame(200, $response->getStatusCode());

        $data = $this->getJson($response);
        $this->assertCount(0, $data);
    }

    public function testListDomainsNonEmpty(): void
    {
        $domains = DomainFactory::createMany(5, ['user_id' => 1]);
        DomainFactory::createMany(1, ['user_id' => 2]);

        $response = $this->consoleApi(
            null,
            'GET',
            '/domains'
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame(5, count($data));
    }
}
