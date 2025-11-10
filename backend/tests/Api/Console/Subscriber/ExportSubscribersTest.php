<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\ExportController;
use App\Entity\SubscriberExport;
use App\Entity\Type\SubscriberExportStatus;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberExportFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ExportController::class)]
#[CoversClass(SubscriberService::class)]
class ExportSubscribersTest extends WebTestCase
{
    public function test_export_subscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();

        // Create some subscribers
        SubscriberFactory::createMany(3, [
            'newsletter' => $newsletter,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/export'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame(SubscriberExportStatus::PENDING->value, $json['status']);

        $subscriberExport = $this->em->getRepository(SubscriberExport::class)->findOneBy(
            [
                'newsletter' => $newsletter->getId()
            ]
        );
        $this->assertNotNull($subscriberExport);
        $this->assertSame(SubscriberExportStatus::PENDING, $subscriberExport->getStatus());

        $this->transport('async')->throwExceptions()->process();

        $subscriberExport = $this->em->getRepository(SubscriberExport::class)
            ->findOneBy(['newsletter' => $newsletter->getId()]);
        $this->assertNotNull($subscriberExport);
        $this->assertNotNull($subscriberExport->getMedia());
        $this->assertSame(SubscriberExportStatus::COMPLETED, $subscriberExport->getStatus());
    }

    public function test_list_exports(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscriberExport = SubscriberExportFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberExportStatus::PENDING,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/export'
        );

        $this->assertSame(200, $response->getStatusCode());
        /** @var array<int, array<string, mixed>> $json */
        $json = $this->getJson();
        $this->assertCount(1, $json);
        $this->assertSame($subscriberExport->getId(), $json[0]['id']);
        $this->assertSame(SubscriberExportStatus::PENDING->value, $json[0]['status']);
        $this->assertNull($json[0]['url']);
    }
}
