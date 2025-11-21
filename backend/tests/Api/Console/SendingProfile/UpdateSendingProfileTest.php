<?php

namespace App\Tests\Api\Console\SendingProfile;

use App\Api\Console\Controller\SendingProfileController;
use App\Api\Console\Object\SendingProfileObject;
use App\Entity\SendingProfile;
use App\Entity\Type\RelayDomainStatus;
use App\Service\SendingProfile\SendingProfileService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendingProfileFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(SendingProfileController::class)]
#[CoversClass(SendingProfileObject::class)]
#[CoversClass(SendingProfileService::class)]
class UpdateSendingProfileTest extends WebTestCase
{
    public function test_update_sending_profile(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $domain1 = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'relay_status' => RelayDomainStatus::ACTIVE,
                'user_id' => 1
            ]
        );

        $domain2 = DomainFactory::createOne(
            [
                'domain' => 'gmail.com',
                'relay_status' => RelayDomainStatus::ACTIVE,
                'user_id' => 1
            ]
        );

        $sendingEmail = SendingProfileFactory::createOne(
            [
                'from_email' => 'thibault@hyvor.com',
                'newsletter' => $newsletter,
                'domain' => $domain1
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/sending-profiles/' . $sendingEmail->getId(),
            [
                'from_email' => 'thibault@gmail.com',
                'brand_name' => 'Hyvor Post',
                'brand_url' => 'https://post.hyvor.com'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('thibault@gmail.com', $json['from_email']);
        $this->assertSame('Hyvor Post', $json['brand_name']);
        $this->assertSame('https://post.hyvor.com', $json['brand_url']);
        $this->assertSame(false, $json['is_default']);

        $sendingEmail = $this->em->getRepository(SendingProfile::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingProfile::class, $sendingEmail);
        $this->assertSame('thibault@gmail.com', $sendingEmail->getFromEmail());
        $this->assertSame('Hyvor Post', $sendingEmail->getBrandName());
        $this->assertSame('https://post.hyvor.com', $sendingEmail->getBrandUrl());
        $this->assertNotNull($sendingEmail->getDomain());
        $this->assertSame($domain2->getId(), $sendingEmail->getDomain()->getId());
        $this->assertSame(false, $sendingEmail->getIsDefault());
        $this->assertSame('2025-02-21 00:00:00', $sendingEmail->getUpdatedAt()->format('Y-m-d H:i:s'));
    }

    public function test_update_default_sending_profile(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $domain1 = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'relay_status' => RelayDomainStatus::ACTIVE,
                'user_id' => 1
            ]
        );

        $domain2 = DomainFactory::createOne(
            [
                'domain' => 'gmail.com',
                'relay_status' => RelayDomainStatus::ACTIVE,
                'user_id' => 1
            ]
        );

        // old default email
        $sendingEmail1 = SendingProfileFactory::createOne(
            [
                'from_email' => 'thibault@hyvor.com',
                'is_default' => true,
                'newsletter' => $newsletter,
                'domain' => $domain1
            ]
        );

        $sendingEmail2 = SendingProfileFactory::createOne(
            [
                'from_email' => 'supun@hyvor.com',
                'is_default' => false,
                'newsletter' => $newsletter,
                'domain' => $domain2
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/sending-profiles/' . $sendingEmail2->getId(),
            [
                'is_default' => true,
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(true, $json['is_default']);
        $this->assertSame($sendingEmail2->getId(), $json['id']);
        $this->assertSame($sendingEmail2->getFromEmail(), $json['from_email']);

        $sendingEmail1 = $this->em->getRepository(SendingProfile::class)->findOneBy(['id' => $sendingEmail1->getId()]);
        $this->assertInstanceOf(SendingProfile::class, $sendingEmail1);
        $this->assertSame(false, $sendingEmail1->getIsDefault());
        $this->assertSame('2025-02-21 00:00:00', $sendingEmail1->getUpdatedAt()->format('Y-m-d H:i:s'));

        $sendingEmail2 = $this->em->getRepository(SendingProfile::class)->findOneBy(['id' => $sendingEmail2->getId()]);
        $this->assertInstanceOf(SendingProfile::class, $sendingEmail2);
        $this->assertSame(true, $sendingEmail2->getIsDefault());
        $this->assertSame('2025-02-21 00:00:00', $sendingEmail2->getUpdatedAt()->format('Y-m-d H:i:s'));
    }
}
