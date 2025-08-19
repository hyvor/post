<?php

namespace App\Tests\Api\Console\Domain;

use App\Api\Console\Controller\DomainController;
use App\Api\Console\Object\DomainObject;
use App\Service\Domain\DomainService;
use App\Service\Integration\Aws\SesService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use Aws\Result;
use Aws\SesV2\SesV2Client;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(DomainController::class)]
#[CoversClass(DomainService::class)]
#[CoversClass(DomainObject::class)]
class VerifyDomainTest extends WebTestCase
{
    private function mockCreateEmailIdentity(): void
    {
        $response = new JsonMockResponse([
            'domain' => 'hyvor.com',
            'dkim_verified' => true,
            'dkim_checked_at' => 1755455400,
        ]);
        $this->container->set(HttpClientInterface::class, new MockHttpClient($response));

//        $sesV2ClientMock = $this->createMock(SesV2Client::class);
//        $sesV2ClientMock->method('__call')->with(
//            'getEmailIdentity',
//            $this->callback(function ($args) {
//                $input = $args[0];
//
//                $this->assertSame('hyvor.com', $input['EmailIdentity']);
//
//                return true;
//            })
//        )
//            ->willReturn(
//                new Result([
//                    'VerifiedForSendingStatus' => true,
//                    'VerificationInfo' => [
//                        'LastCheckedTimestamp' => '2025-02-21T00:00:00Z',
//                        'error_type' => 'None',
//                    ]
//                ])
//            );
//
//        $sesServiceMock = $this->createMock(SesService::class);
//        $sesServiceMock->method('getClient')->willReturn($sesV2ClientMock);
//        $this->container->set(SesService::class, $sesServiceMock);
    }

    public function test_verify_domain(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 1,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/' . $domain->getId() . '/verify',
            useSession: true
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsArray($json['domain']);
        $this->assertSame('hyvor.com', $json['domain']['domain']);
        $this->assertTrue($json['domain']['verified_in_relay']);

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'Your domain hyvor.com is verified');
        $this->assertEmailHtmlBodyContains(
            $email,
            'Your domain <strong>hyvor.com</strong> has been successfully verified'
        );
    }

    public function test_already_verified(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'verified_in_relay' => true,
                'user_id' => 1,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/' . $domain->getId() . '/verify',
            useSession: true
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Domain already verified', $json['message']);
    }

    public function test_domain_not_found(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/99999/verify',
            useSession: true
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Domain not found', $json['message']);
    }

    public function test_user_can_only_verify_their_domain(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => 2,
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains/' . $domain->getId() . '/verify',
            useSession: true
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('You are not the owner of this domain', $json['message']);
    }
}
