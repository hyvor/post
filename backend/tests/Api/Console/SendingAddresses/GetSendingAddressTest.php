<?php

namespace App\Tests\Api\Console\SendingAddresses;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendingAddressFactory;

class GetSendingAddressTest extends WebTestCase
{
    public function test_get_sending_email_test(): void
    {
        $project = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne([
            'verified_in_ses' => true,
        ]);

        $sendingAddress = SendingAddressFactory::createOne([
            'project' => $project,
            'domain' => $domain,
            'email' => 'test@hyvor.com',
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/sending-addresses'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertCount(1, $json);
        $item = $json[0];
        $this->assertSame($sendingAddress->getId(), $item['id']);
        $this->assertSame('test@hyvor.com', $item['email']);
        $this->assertSame($domain->getId(), $item['domain']['id']);
    }
}
