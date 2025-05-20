<?php

namespace App\Tests\Api\Console\SendingAddresses;

use App\Api\Console\Controller\SendingAddressController;
use App\Api\Console\Object\SendingAddressObject;
use App\Entity\SendingAddress;
use App\Service\SendingEmail\SendingAddressService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\ProjectFactory;
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

        $project = ProjectFactory::createOne();

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
                'project' => $project,
                'domain' => $domain1
            ]
        );

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/sending-addresses/' . $sendingEmail->getId(),
            [
                'email' => 'thibault@gmail.com',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame('thibault@gmail.com', $json['email']);
        $this->assertIsArray($json['domain']);
        $this->assertSame($domain2->getId(), $json['domain']['id']);

        $sendingEmail = $this->em->getRepository(SendingAddress::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingAddress::class, $sendingEmail);
        $this->assertSame('thibault@gmail.com', $sendingEmail->getEmail());
        $this->assertSame($domain2->getId(), $sendingEmail->getDomain()->getId());
        $this->assertSame('2025-02-21 00:00:00', $sendingEmail->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
