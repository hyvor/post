<?php

namespace App\Tests\Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Service\Domain\DomainService;
use App\Service\Integration\Aws\SesService;
use App\Tests\Case\WebTestCase;
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


    private function mockCreateEmailIdentity(): void
    {
        $sesV2ClientMock = $this->createMock(SesV2Client::class);
        $sesV2ClientMock->method('__call')->with(
            'createEmailIdentity',
            $this->callback(function ($args) {

                $input = $args[0];

                $this->assertSame('hyvor.com', $input['EmailIdentity']);
                $this->assertSame('hyvor-post', $input['DkimSigningAttributes']['DomainSigningSelector']);
                $this->assertIsString($input['DkimSigningAttributes']['DomainSigningPrivateKey']);

                return true;
            })
        );

        $sesServiceMock = $this->createMock(SesService::class);
        $sesServiceMock->method('getClient')->willReturn($sesV2ClientMock);
        $this->container->set(SesService::class, $sesServiceMock);
    }

    public function test_create_domain(): void
    {
        $this->mockCreateEmailIdentity();

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
