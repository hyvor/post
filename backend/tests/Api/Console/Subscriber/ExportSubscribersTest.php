<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\SubscriberExport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberExportStatus;
use App\Service\Media\MediaService;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
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
            '/subscribers/export'
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Export started', $json['message']);

        $subscriberExport = $this->em->getRepository(SubscriberExport::class)->findOneBy
            (
                [
                    'newsletter' => $newsletter->getId()
                ]
            );
        $this->assertNotNull($subscriberExport);
        $this->assertSame(SubscriberExportStatus::PENDING, $subscriberExport->getStatus());

        $this->transport()->throwExceptions()->process();

        $subscriberExport = $this->em->getRepository(SubscriberExport::class)
            ->findOneBy(['newsletter' => $newsletter->getId()]);
        $this->assertNotNull($subscriberExport);
        $this->assertNotNull($subscriberExport->getMedia());
        $this->assertSame(SubscriberExportStatus::COMPLETED, $subscriberExport->getStatus());
    }
}
