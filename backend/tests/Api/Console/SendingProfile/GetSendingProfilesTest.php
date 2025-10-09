<?php

namespace App\Tests\Api\Console\SendingProfile;

use App\Api\Console\Controller\SendingProfileController;
use App\Api\Console\Object\SendingProfileObject;
use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Entity\Type\RelayDomainStatus;
use App\Service\SendingProfile\SendingProfileService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendingProfileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SendingProfileController::class)]
#[CoversClass(SendingProfileObject::class)]
#[CoversClass(SendingProfileService::class)]
class GetSendingProfilesTest extends WebTestCase
{
    public function test_get_sending_profile_test(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne([
            'relay_status' => RelayDomainStatus::ACTIVE,
        ]);

        $sendingProfile = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'domain' => $domain,
            'from_email' => 'test@hyvor.com',
        ]);

        SendingProfileFactory::createMany(2, [
            'newsletter' => NewsletterFactory::createOne(),
            'domain' => DomainFactory::createOne([
                'relay_status' => RelayDomainStatus::ACTIVE,
            ])
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/sending-profiles'
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $item = $json[0];
        $this->assertSame($sendingProfile->getId(), $item['id']);
        $this->assertSame('test@hyvor.com', $item['from_email']);
    }

    public function test_get_system_profile(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $sendingProfile = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'from_email' => 'system@email.com',
            'from_name' => null,
            'reply_to_email' => null,
            'brand_name' => null,
            'brand_logo' => null,
            'is_system' => true
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/sending-profiles'
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $item = $json[0];
        $this->assertSame($sendingProfile->getId(), $item['id']);
        $this->assertSame('system@email.com', $item['from_email']);
        $this->assertTrue($item['is_system']);
    }
}
