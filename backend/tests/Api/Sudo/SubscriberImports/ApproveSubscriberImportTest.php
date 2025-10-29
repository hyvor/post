<?php

namespace App\Tests\Api\Sudo\SubscriberImports;

use App\Api\Sudo\Controller\SubscriberImportController;
use App\Api\Sudo\Object\SubscriberImportObject;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberImportFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberImportController::class)]
#[CoversClass(ImportService::class)]
#[CoversClass(ImportSubscribersMessage::class)]
#[CoversClass(SubscriberImportObject::class)]
class ApproveSubscriberImportTest extends WebTestCase
{
    public function test_approve_subscriber_import(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberImportStatus::PENDING_APPROVAL,
        ]);

        $response = $this->sudoApi(
            'POST',
            '/subscriber-imports/' . $subscriberImport->getId(),
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($subscriberImport->getId(), $data['id']);

        $this->assertSame(SubscriberImportStatus::IMPORTING, $subscriberImport->getStatus());

        $this->transport('async')->queue()->assertCount(1);
        $message = $this->transport('async')->queue()->first()->getMessage();
        $this->assertInstanceOf(ImportSubscribersMessage::class, $message);
    }
}