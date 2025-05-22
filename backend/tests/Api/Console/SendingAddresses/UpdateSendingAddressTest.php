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
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(SendingAddressController::class)]
#[CoversClass(SendingAddressObject::class)]
#[CoversClass(SendingAddressService::class)]
class UpdateSendingAddressTest extends WebTestCase
{
    public function test_update_sending_email(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $domain1 = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'verified_in_ses' => true,
                'user_id' => 1
            ]
        );

        $domain2 = DomainFactory::createOne(
            [
                'domain' => 'gmail.com',
                'verified_in_ses' => true,
                'user_id' => 1
            ]
        );

        $sendingEmail = SendingAddressFactory::createOne(
            [
                'email' => 'thibault@hyvor.com',
                'newsletter' => $newsletter,
                'domain' => $domain1
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/sending-addresses/' . $sendingEmail->getId(),
            [
                'email' => 'thibault@gmail.com',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertSame('thibault@gmail.com', $json['email']);
        $this->assertIsArray($json['domain']);
        $this->assertSame($domain2->getId(), $json['domain']['id']);
        $this->assertSame(false, $json['is_default']);

        $sendingEmail = $this->em->getRepository(SendingAddress::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingAddress::class, $sendingEmail);
        $this->assertSame('thibault@gmail.com', $sendingEmail->getEmail());
        $this->assertSame($domain2->getId(), $sendingEmail->getDomain()->getId());
        $this->assertSame(false, $sendingEmail->isDefault());
        $this->assertSame('2025-02-21 00:00:00', $sendingEmail->getUpdatedAt()->format('Y-m-d H:i:s'));
    }

    public function test_update_default_sending_address(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $domain1 = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'verified_in_ses' => true,
                'user_id' => 1
            ]
        );

        $domain2 = DomainFactory::createOne(
            [
                'domain' => 'gmail.com',
                'verified_in_ses' => true,
                'user_id' => 1
            ]
        );

        // old default email
        $sendingEmail1 = SendingAddressFactory::createOne(
            [
                'email' => 'thibault@hyvor.com',
                'is_default' => true,
                'newsletter' => $newsletter,
                'domain' => $domain1
            ]
        );

        $sendingEmail2 = SendingAddressFactory::createOne(
            [
                'email' => 'supun@hyvor.com',
                'is_default' => false,
                'newsletter' => $newsletter,
                'domain' => $domain2
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/sending-addresses/' . $sendingEmail2->getId(),
            [
                'is_default' => true,
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(true, $json['is_default']);
        $this->assertSame($sendingEmail2->getId(), $json['id']);
        $this->assertSame($sendingEmail2->getEmail(), $json['email']);

        $sendingEmail1 = $this->em->getRepository(SendingAddress::class)->findOneBy(['id' => $sendingEmail1->getId()]);
        $this->assertInstanceOf(SendingAddress::class, $sendingEmail1);
        $this->assertSame(false, $sendingEmail1->isDefault());
        $this->assertSame('2025-02-21 00:00:00', $sendingEmail1->getUpdatedAt()->format('Y-m-d H:i:s'));

        $sendingEmail2 = $this->em->getRepository(SendingAddress::class)->findOneBy(['id' => $sendingEmail2->getId()]);
        $this->assertInstanceOf(SendingAddress::class, $sendingEmail2);
        $this->assertSame(true, $sendingEmail2->isDefault());
        $this->assertSame('2025-02-21 00:00:00', $sendingEmail2->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
