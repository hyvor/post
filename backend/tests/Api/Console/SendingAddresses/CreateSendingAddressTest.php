<?php

namespace App\Tests\Api\Console\SendingAddresses;

use App\Api\Console\Controller\SendingAddressController;
use App\Api\Console\Object\SendingAddressObject;
use App\Entity\SendingAddress;
use App\Service\SendingEmail\SendingAddressService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendingAddressFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SendingAddressController::class)]
#[CoversClass(SendingAddressObject::class)]
#[CoversClass(SendingAddressService::class)]
class CreateSendingAddressTest extends WebTestCase
{
    public function test_create_sending_email(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne([
                'domain' => 'hyvor.com',
                'verified_in_ses' => true,
                'user_id' => 1
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/sending-addresses',
            [
                'email' => 'thibault@hyvor.com'
            ],
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertSame('thibault@hyvor.com', $json['email']);
        $this->assertSame(true, $json['is_default']);

        $sendingEmail = $this->em->getRepository(SendingAddress::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingAddress::class, $sendingEmail);
        $this->assertSame('thibault@hyvor.com', $sendingEmail->getEmail());
        $this->assertSame(true, $sendingEmail->isDefault());
    }

    public function test_it_does_not_make_it_default_when_there_is_already_one(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne([
                'domain' => 'hyvor.com',
                'verified_in_ses' => true,
                'user_id' => 1
            ]
        );

        SendingAddressFactory::createOne([
            'newsletter' => $newsletter,
            'domain' => $domain
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/sending-addresses',
            [
                'email' => 'thibault@hyvor.com'
            ],
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(false, $json['is_default']);

        $sendingEmail = $this->em->getRepository(SendingAddress::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingAddress::class, $sendingEmail);
        $this->assertSame(false, $sendingEmail->isDefault());
    }

    public function test_create_sending_email_domain_not_found(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/sending-addresses',
            [
                'email' => 'thibault@nonexistent.com'
            ],
        );
        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertSame('Domain not found', $json['message']);
    }

    public function test_create_sending_email_domain_not_verified(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $domain = DomainFactory::createOne([
            'domain' => 'hyvor.com',
            'verified_in_ses' => false,
            'user_id' => 1
        ]);
        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/sending-addresses',
            [
                'email' => 'thibault@hyvor.com'
            ],
        );
        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Domain is not verified', $json['message']);
    }
}
