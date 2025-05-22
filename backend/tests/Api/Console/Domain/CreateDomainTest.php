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
use App\Tests\Factory\NewsletterFactory;
use Aws\Command;
use Aws\Exception\AwsException;
use Aws\SesV2\SesV2Client;
use Doctrine\Common\Collections\ArrayCollection;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
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

        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains',
            [
                'domain' => 'hyvor.com',
            ]
        );
        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $domainId = $json['id'];
        $this->assertIsInt($domainId);
        $this->assertSame('hyvor.com', $json['domain']);

        $domainRepository = $this->em->getRepository(Domain::class);
        $domain = $domainRepository->find($domainId);
        $this->assertNotNull($domain);
        $this->assertSame('hyvor.com', $domain->getDomain());
    }

    public function test_create_domain_invalid(): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/domains',
            [
                'domain' => 'invalid-domain',
            ]
        );
        $this->assertSame(422, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsArray($json['violations']);
        $violation = $json['violations'][0];
        $this->assertIsArray($violation);
        $this->assertSame('The domain must be a valid domain name.', $violation['message']);
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_create_domain_already_exists(bool $current): void
    {
        $this->mockCreateEmailIdentity();

        Clock::set(new MockClock('2025-02-21'));

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'user_id' => $current ? 1 : 2
            ]
        );

        $response = $this->consoleApi(
            null,
            'POST',
            '/domains',
            [
                'domain' => 'hyvor.com',
            ]
        );
        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(
            $current ?
                'This domain is already registered' :
                'This domain is already registered by another user',
            $json['message']
        );
    }

    public function test_error_on_aws_call_fails_and_logs(): void
    {
        $sesV2ClientMock = $this->createMock(SesV2Client::class);
        $sesV2ClientMock->method('__call')
            ->willThrowException(new AwsException('Bad API key', new Command('CreateEmailIdentity')));

        $sesServiceMock = $this->createMock(SesService::class);
        $sesServiceMock->method('getClient')->willReturn($sesV2ClientMock);
        $this->container->set(SesService::class, $sesServiceMock);

        $response = $this->consoleApi(
            null,
            'POST',
            '/domains',
            [
                'domain' => 'hyvor.com',
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Failed to create domain. Contact support for more details', $json['message']);

        // logging
        $testLogger = $this->getTestLogger();
        $this->assertTrue($testLogger->hasCriticalThatContains('Failed to create email domain in AWS SES'));
        $record = new ArrayCollection($testLogger->getRecords())
            ->findFirst(fn($index, $record) => $record->message === 'Failed to create email domain in AWS SES');
        $this->assertNotNull($record);
        $this->assertSame('hyvor.com', $record->context['domain']);
        $this->assertInstanceOf(AwsException::class, $record->context['error']);
    }
}
