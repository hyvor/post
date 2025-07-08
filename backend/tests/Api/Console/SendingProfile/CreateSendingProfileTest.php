<?php

namespace App\Tests\Api\Console\SendingProfile;

use App\Api\Console\Controller\SendingProfileController;
use App\Api\Console\Object\SendingProfileObject;
use App\Entity\SendingProfile;
use App\Service\SendingProfile\SendingProfileService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendingProfileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SendingProfileController::class)]
#[CoversClass(SendingProfileObject::class)]
#[CoversClass(SendingProfileService::class)]
class CreateSendingProfileTest extends WebTestCase
{
    public function test_create_sending_profile(): void
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
            '/sending-profiles',
            [
                'from_email' => 'thibault@hyvor.com',
                'from_name' => ''
            ],
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();

        $this->assertSame('thibault@hyvor.com', $json['from_email']);
        $this->assertSame(true, $json['is_default']);

        $sendingEmail = $this->em->getRepository(SendingProfile::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingProfile::class, $sendingEmail);
        $this->assertSame('thibault@hyvor.com', $sendingEmail->getFromEmail());
        $this->assertNull($sendingEmail->getFromName());
        $this->assertSame(true, $sendingEmail->getIsDefault());
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

        SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'domain' => $domain
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/sending-profiles',
            [
                'email' => 'thibault@hyvor.com'
            ],
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(false, $json['is_default']);

        $sendingEmail = $this->em->getRepository(SendingProfile::class)->findOneBy(['id' => $json['id']]);
        $this->assertInstanceOf(SendingProfile::class, $sendingEmail);
        $this->assertSame(false, $sendingEmail->getIsDefault());
    }

    public function test_create_sending_email_domain_not_found(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/sending-profiles',
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
            '/sending-profiles',
            [
                'email' => 'thibault@hyvor.com'
            ],
        );
        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Domain is not verified', $json['message']);
    }
}
