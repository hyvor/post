<?php

namespace App\Tests\Api\Console\SendingEmail;

use App\Api\Console\Controller\SendingEmailController;
use App\Api\Console\Object\SendingEmailObject;
use App\Entity\SendingEmail;
use App\Service\SendingEmail\SendingEmailService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SendingEmailFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(SendingEmailController::class)]
#[CoversClass(SendingEmailObject::class)]
#[CoversClass(SendingEmailService::class)]
class UpdateSendingEmailTest extends WebTestCase
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

        $sendingEmail = SendingEmailFactory::createOne(
            [
                'email' => 'thibault@hyvor.com',
                'project' => $project,
                'custom_domain' => $domain1
            ]
        );

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/sending-emails/' . $sendingEmail->getId(),
            [
                'email' => 'thibault@gmail.com',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame('thibault@gmail.com', $json['email']);
        $this->assertIsArray($json['domain']);
        $this->assertSame($domain2->getId(), $json['domain']['id']);

        $sendingEmail = $this->em->getRepository(SendingEmail::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingEmail::class, $sendingEmail);
        $this->assertSame('thibault@gmail.com', $sendingEmail->getEmail());
        $this->assertSame($domain2->getId(), $sendingEmail->getCustomDomain()->getId());
        $this->assertSame('2025-02-21 00:00:00', $sendingEmail->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
