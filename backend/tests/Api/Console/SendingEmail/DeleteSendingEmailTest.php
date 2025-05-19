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

#[CoversClass(SendingEmailController::class)]
#[CoversClass(SendingEmailService::class)]
class DeleteSendingEmailTest extends WebTestCase
{
    public function test_delete_sending_email(): void
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

        $id = $sendingEmail->getId();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/sending-emails/' . $sendingEmail->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->getJson($response);

        $repository = $this->em->getRepository(SendingEmail::class);
        $deletedSendingEmail = $repository->findOneBy(['id' => $id]);
        $this->assertNull($deletedSendingEmail);
    }

    public function test_delete_sending_email_not_found(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'DELETE',
            '/sending-emails/1'
        );

        $this->assertSame(404, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertSame('Entity not found', $json['message']);
    }
}
