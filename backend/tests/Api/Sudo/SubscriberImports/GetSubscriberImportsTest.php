<?php

namespace App\Tests\Api\Sudo\SubscriberImports;

use App\Api\Sudo\Controller\SubscriberImportController;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\ImportService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberImportFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberImportController::class)]
#[CoversClass(ImportService::class)]
class GetSubscriberImportsTest extends WebTestCase
{
    public function test_get_subscriber_imports(): void
    {
        SubscriberImportFactory::createMany(5, [
            'status' => SubscriberImportStatus::PENDING_APPROVAL
        ]);
        SubscriberImportFactory::createMany(2, [
            'status' => SubscriberImportStatus::COMPLETED
        ]);
        SubscriberImportFactory::createMany(4, [
            'status' => SubscriberImportStatus::REQUIRES_INPUT
        ]);

        $response = $this->sudoApi(
            'GET',
            '/subscriber-imports'
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertCount(5, $data);

        $subscriberImport = $data[0];
        $this->assertIsArray($subscriberImport);
        $this->assertCount(6, $subscriberImport);
        $this->assertArrayHasKey('id', $subscriberImport);
        $this->assertArrayHasKey('created_at', $subscriberImport);
        $this->assertArrayHasKey('newsletter_subdomain', $subscriberImport);
        $this->assertArrayHasKey('total_rows', $subscriberImport);
        $this->assertArrayHasKey('source', $subscriberImport);
        $this->assertArrayHasKey('columns', $subscriberImport);
    }

    public function test_get_subscriber_imports_by_subdomain(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberImportStatus::PENDING_APPROVAL,
        ]);
        SubscriberImportFactory::createMany(3, [
            'status' => SubscriberImportStatus::PENDING_APPROVAL,
        ]);

        $response = $this->sudoApi(
            'GET',
            "/subscriber-imports?subdomain={$newsletter->getSubdomain()}"
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertCount(1, $data);

        $import = $data[0];
        $this->assertIsArray($import);
        $this->assertSame($subscriberImport->getId(), $import['id']);
    }
}