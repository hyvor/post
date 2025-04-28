<?php

namespace Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Service\Domain\DomainService;
use App\Service\Integration\Aws\SnsValidationService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use Aws\SesV2\SesV2Client;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
#[CoversClass(CreateDomainInput::class)]
#[CoversClass(DomainObject::class)]
class CreateDomainTest extends WebTestCase
{


    private function mockDomainCreation(): void
    {
        $clientMock = $this->createMock(DomainService::class);
        $clientMock->method('createAwsDomain')->willReturn(true);
        $this->container->set(DomainService::class, $clientMock);
    }

    public function test_create_domain(): void
    {
        $this->mockDomainCreation();

        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/domains',
            [
                'domain' => 'hyvor.com',
            ]
        );
        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $domainId = $json['id'];
        $this->assertIsInt($domainId);
        $this->assertSame('hyvor.com', $json['domain']);
    }
}
