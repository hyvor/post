<?php

namespace Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Entity\Domain;
use App\Service\Domain\DomainService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
class DeleteDomainTest extends WebTestCase
{
    public function testDeleteDomain(): void
    {
        $project = ProjectFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
            ]
        );

        $domain_id = $domain->getId();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/domains/' . $domain->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);

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
    }
}
