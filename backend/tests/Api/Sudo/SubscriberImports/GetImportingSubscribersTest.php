<?php

namespace App\Tests\Api\Sudo\SubscriberImports;

use App\Api\Sudo\Controller\SubscriberImportController;
use App\Api\Sudo\Object\ImportingSubscriberObject;
use App\Entity\SubscriberImport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Media\MediaService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberImportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(SubscriberImportController::class)]
#[CoversClass(ImportingSubscriberObject::class)]
class GetImportingSubscribersTest extends WebTestCase
{
    private function uploadImportFile(): SubscriberImport
    {
        $newsletter = NewsletterFactory::createOne();

        $file = new UploadedFile(
            dirname(__DIR__, 3) . '/Service/Import/import.csv',
            'import.csv',
            mimeType: "text/csv",
        );

        /** @var MediaService $mediaService */
        $mediaService = $this->container->get(MediaService::class);
        $media = $mediaService->upload(
            $newsletter->_real(),
            MediaFolder::IMPORT,
            $file,
        );

        return SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'media' => $media,
            'status' => SubscriberImportStatus::PENDING_APPROVAL,
            'fields' => [
                'email' => 'email',
                'lists' => 'lists'
            ],
            'csv_fields' => ['email', 'lists', 'extra_col_1', 'extra_col_2'],

        ]);
    }

    public function test_get_importing_subscribers(): void
    {
        $subscriberImport = $this->uploadImportFile();

        $response = $this->sudoApi(
            'GET',
            '/subscriber-imports/' . $subscriberImport->getId(),
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertCount(3, $data);

        $importingSubscriber = $data[0];
        $this->assertIsArray($importingSubscriber);
        $this->assertArrayHasKey('email', $importingSubscriber);
        $this->assertSame('john@hyvor.com', $importingSubscriber['email']);
        $this->assertArrayHasKey('lists', $importingSubscriber);
        $this->assertArrayHasKey('status', $importingSubscriber);
        $this->assertArrayHasKey('subscribed_at', $importingSubscriber);
        $this->assertArrayHasKey('subscribe_ip', $importingSubscriber);
        $this->assertArrayHasKey('metadata', $importingSubscriber);
    }

    public function test_get_importing_subscribers_with_limit_and_offset(): void
    {
        $subscriberImport = $this->uploadImportFile();

        $response = $this->sudoApi(
            'GET',
            '/subscriber-imports/' . $subscriberImport->getId() . '?limit=2&offset=1',
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $importingSubscriber1 = $data[0];
        $this->assertIsArray($importingSubscriber1);
        $this->assertArrayHasKey('email', $importingSubscriber1);
        $this->assertSame('jane@hyvor.com', $importingSubscriber1['email']);

        $importingSubscriber2 = $data[1];
        $this->assertIsArray($importingSubscriber2);
        $this->assertArrayHasKey('email', $importingSubscriber2);
        $this->assertSame('doe@hyvor.com', $importingSubscriber2['email']);
    }
}