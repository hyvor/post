<?php

namespace App\Tests\Api\Console\Import;

use App\Entity\Type\MediaFolder;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Media\MediaService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\SubscriberImportFactory;
use App\Entity\Type\SubscriberImportStatus;
use App\Tests\Factory\NewsletterFactory;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportTest extends WebTestCase
{
    use ClockSensitiveTrait;

    /** @var array<string, string> */
    const array MAPPING = [
        'email' => 'email',
        'lists' => 'lists'
    ];

    public function test_import(): void
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

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'media' => $media,
            'status' => SubscriberImportStatus::REQUIRES_INPUT
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/imports/' . $subscriberImport->getId(),
            [
                'mapping' => self::MAPPING
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $this->transport('async')->queue()->assertCount(1);
        $message = $this->transport('async')->queue()->first()->getMessage();
        $this->assertInstanceOf(ImportSubscribersMessage::class, $message);

        $this->transport('async')->throwExceptions()->process();

        $this->assertSame(SubscriberImportStatus::COMPLETED, $subscriberImport->getStatus());
        $this->assertSame(self::MAPPING, $subscriberImport->getFields());
    }

    public function test_import_in_non_pending_status(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberImportStatus::COMPLETED
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/imports/' . $subscriberImport->getId(),
            [
                'mapping' => self::MAPPING
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('Import is not in pending status.', $content);
    }

    public function test_import_without_email_mapping(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberImportStatus::REQUIRES_INPUT
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/imports/' . $subscriberImport->getId(),
            [
                'mapping' => [
                    'lists' => 'lists'
                ]
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertHasViolation('mapping', 'The mapping must contain the key "email".');
    }

    public function test_import_with_null_email_mapping(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberImportStatus::REQUIRES_INPUT
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/imports/' . $subscriberImport->getId(),
            [
                'mapping' => [
                    'email' => null
                ]
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertHasViolation('mapping', 'The mapping must contain the key "email".');
    }

    public function test_import_with_empty_email_mapping(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberImportStatus::REQUIRES_INPUT
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/imports/' . $subscriberImport->getId(),
            [
                'mapping' => [
                    'email' => ''
                ]
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertHasViolation('mapping', 'The mapping must contain the key "email".');
    }

    public function test_daily_import_limit(): void
    {
        $date = new \DateTimeImmutable('2025-10-23 12:00:00');
        static::mockTime($date);

        $newsletter = NewsletterFactory::createOne();
        SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'created_at' => $date,
            'status' => SubscriberImportStatus::COMPLETED
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/imports/upload',
            parameters: [
                'source' => 'test'
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('Daily import limit reached.', $content);
    }

    public function test_monthly_import_limit(): void
    {
        $date = new \DateTimeImmutable('2025-10-23 12:00:00');
        static::mockTime($date);

        $newsletter = NewsletterFactory::createOne();
        SubscriberImportFactory::createMany(5, [
            'newsletter' => $newsletter,
            'created_at' => $date->modify('-7 day'),
            'status' => SubscriberImportStatus::COMPLETED
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/imports/upload',
            parameters: [
                'source' => 'test'
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertStringContainsString('Monthly import limit reached.', $content);
    }


    public function test_import_upload_small(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $file = new UploadedFile(
            dirname(__DIR__, 3) . '/Service/Import/importsmall.csv',
            'import.csv',
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/imports/upload',
            files: [
                'file' => $file
            ],
            parameters: [
                'source' => 'test'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
    }
}
