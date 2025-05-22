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
#[CoversClass(SendingAddressService::class)]
class DeleteSendingAddressTest extends WebTestCase
{
    public function test_delete_sending_email(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne([
            'verified_in_ses' => true,
        ]);

        $sendingEmail = SendingAddressFactory::createOne([
            'newsletter' => $newsletter,
            'domain' => $domain,
            'email' => 'test@hyvor.com',
        ]);

        $id = $sendingEmail->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-addresses/' . $sendingEmail->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->getJson($response);

        $repository = $this->em->getRepository(SendingAddress::class);
        $deletedSendingEmail = $repository->findOneBy(['id' => $id]);
        $this->assertNull($deletedSendingEmail);
    }

    public function test_delete_sending_email_not_found(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-addresses/1'
        );

        $this->assertSame(404, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertSame('Entity not found', $json['message']);
    }
}
