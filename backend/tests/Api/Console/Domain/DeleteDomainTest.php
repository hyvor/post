<?php

namespace App\Tests\Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Entity\Domain;
use App\Service\Domain\DomainService;
use App\Service\Integration\Aws\SesService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\ProjectFactory;
use Aws\SesV2\SesV2Client;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
class DeleteDomainTest extends WebTestCase
{
    private function mockDeleteDomainEntity(): void
    {
        $sesV2ClientMock = $this->createMock(SesV2Client::class);
        $sesV2ClientMock->method('__call')->with(
            'deleteEmailIdentity',
            $this->callback(function ($args) {

                $input = $args[0];

                $this->assertSame('hyvor.com', $input['EmailIdentity']);
                return true;
            })
        );

        $sesServiceMock = $this->createMock(SesService::class);
        $sesServiceMock->method('getClient')->willReturn($sesV2ClientMock);
        $this->container->set(SesService::class, $sesServiceMock);
    }

    public function testDeleteDomain(): void
    {
        $this->mockDeleteDomainEntity();
        $project = ProjectFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 1,
            ]
        );

        $domain_id = $domain->getId();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/domains/' . $domain->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->getJson();

        $repository = $this->em->getRepository(Domain::class);
        $find = $repository->find($domain_id);
        $this->assertNull($find);
    }

    public function testDeleteDomainNotFound(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/domains/123456789'
        );

        $this->assertSame(400, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertSame('Domain not found', $json['message']);
    }

    public function test_user_can_only_delete_their_domains(): void
    {
        $this->mockDeleteDomainEntity();
        $project = ProjectFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 2,
            ]
        );

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/domains/' . $domain->getId(),
        );

        $this->assertSame(400, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertSame('You are not the owner of this domain', $json['message']);
    }
}
