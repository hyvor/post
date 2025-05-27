<?php

namespace App\Tests\Api\Console\SendingProfile;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendingProfileFactory;

class GetSendingProfilesTest extends WebTestCase
{
    public function test_get_sending_email_test(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne([
            'verified_in_ses' => true,
        ]);

        $sendingProfile = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'domain' => $domain,
            'from_email' => 'test@hyvor.com',
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/sending-profiles'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(1, $json);
        $item = $json[0];
        $this->assertSame($sendingProfile->getId(), $item['id']);
        $this->assertSame('test@hyvor.com', $item['from_email']);
    }
}
