<?php

namespace App\Tests\Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Object\DomainObject;
use App\Service\Domain\DomainService;
use App\Service\Integration\Aws\SesService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\ProjectFactory;
use Aws\Result;
use Aws\SesV2\SesV2Client;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
#[CoversClass(DomainObject::class)]
class VerifyDomainTest extends WebTestCase
{
    private function mockCreateEmailIdentity(): void
    {
        $sesV2ClientMock = $this->createMock(SesV2Client::class);
        $sesV2ClientMock->method('__call')->with(
            'getEmailIdentity',
            $this->callback(function ($args) {

                $input = $args[0];

                $this->assertSame('hyvor.com', $input['EmailIdentity']);

                return true;
            })
        )
            ->willReturn(
                new Result([
                    'VerifiedForSendingStatus' => true,
                    'VerificationInfo' => [
                        'LastCheckedTimestamp' => '2025-02-21T00:00:00Z',
                        'error_type' => 'None',
                    ]
                ])
            )
        ;

        $sesServiceMock = $this->createMock(SesService::class);
        $sesServiceMock->method('getClient')->willReturn($sesV2ClientMock);
        $this->container->set(SesService::class, $sesServiceMock);
    }

    public function test_verify_domain(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 1,
            ]
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            '/domains/verify/' . $domain->getId(),
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertIsArray($json['domain']);
        $this->assertSame('hyvor.com', $json['domain']['domain']);
        $this->assertTrue($json['domain']['verified_in_ses']);

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'Your domain hyvor.com is verified');
    }

    public function test_already_verified(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'verified_in_ses' => true,
                'user_id' => 1,
            ]
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            '/domains/verify/' . $domain->getId(),
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Domain already verified', $json['message']);
    }

    public function test_domain_not_found(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/domains/verify/99999',
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Domain not found', $json['message']);
    }

    public function test_user_can_only_verify_their_domain(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 2,
            ]
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            '/domains/verify/' . $domain->getId(),
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('You are not the owner of this domain', $json['message']);
    }
}
