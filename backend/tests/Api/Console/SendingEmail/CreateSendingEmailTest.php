<?php

namespace App\Tests\Api\Console\SendingEmail;

use App\Api\Console\Controller\SendingEmailController;
use App\Api\Console\Object\SendingEmailObject;
use App\Entity\SendingAddress;
use App\Service\SendingEmail\SendingEmailService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SendingEmailController::class)]
#[CoversClass(SendingEmailObject::class)]
#[CoversClass(SendingEmailService::class)]
class CreateSendingEmailTest extends WebTestCase
{
    public function test_create_sending_email(): void
    {
        $project = ProjectFactory::createOne();

        $domain = DomainFactory::createOne([
                'domain' => 'hyvor.com',
                'verified_in_ses' => true,
                'user_id' => 1
            ]
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            '/sending-emails',
            [
                'email' => 'thibault@hyvor.com'
            ],
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame('thibault@hyvor.com', $json['email']);

        $sendingEmail = $this->em->getRepository(SendingAddress::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingAddress::class, $sendingEmail);
        $this->assertSame('thibault@hyvor.com', $sendingEmail->getEmail());
    }

    public function test_create_sending_email_domain_not_found(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/sending-emails',
            [
                'email' => 'thibault@nonexistent.com'
            ],
        );
        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame('Domain not found', $json['message']);
    }

    public function test_create_sending_email_domain_not_verified(): void
    {
        $project = ProjectFactory::createOne();
        $domain = DomainFactory::createOne([
            'domain' => 'hyvor.com',
            'verified_in_ses' => false,
            'user_id' => 1
        ]);
        $response = $this->consoleApi(
            $project,
            'POST',
            '/sending-emails',
            [
                'email' => 'thibault@hyvor.com'
            ],
        );
        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Domain is not verified', $json['message']);
    }
}
