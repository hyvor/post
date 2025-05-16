<?php

namespace App\Tests\Api\Console\SendingEmail;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SendingEmailFactory;

class GetSendingEmailTest extends WebTestCase
{
    public function test_get_sending_email_test(): void
    {
        $project = ProjectFactory::createOne();

        $domain = DomainFactory::createOne([
            'verified_in_ses' => true,
        ]);

        $sendingEmail = SendingEmailFactory::createOne([
            'project' => $project,
            'custom_domain' => $domain,
            'email' => 'test@hyvor.com',
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/sending-emails'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(1, $json);
        $item = $json[0];
        $this->assertSame($sendingEmail->getId(), $item['id']);
        $this->assertSame('test@hyvor.com', $item['email']);
        $this->assertSame($domain->getId(), $item['customDomain']['id']);
    }
}
