<?php

namespace App\Tests\Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Entity\Domain;
use App\Service\Domain\DomainService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use Aws\SesV2\SesV2Client;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
class DeleteDomainTest extends WebTestCase
{
    private function mockDeleteDomainEntity(): void
    {
        $response = new JsonMockResponse();
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));
    }

    public function testDeleteDomain(): void
    {
        $this->mockDeleteDomainEntity();
        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'organization_id' => 1,
            ]
        );

        $domain_id = $domain->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/domains/' . $domain->getId(),
            useSession: true
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->getJson();

        $repository = $this->em->getRepository(Domain::class);
        $find = $repository->find($domain_id);
        $this->assertNull($find);
    }

    public function testDeleteDomainNotFound(): void
    {
        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/domains/123456789',
            useSession: true

        );

        $this->assertSame(400, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertSame('Domain not found', $json['message']);
    }

    public function test_user_can_only_delete_their_domains(): void
    {
        $this->mockDeleteDomainEntity();
        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'organization_id' => 2,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/domains/' . $domain->getId(),
            useSession: true

        );

        $this->assertSame(400, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertSame('Your current organization does not own this domain', $json['message']);
    }
}
