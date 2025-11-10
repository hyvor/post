<?php

namespace App\Tests\MessageHandler\Import;

use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\App\Messenger\ClearWorkerMemoryEventListener;
use App\Service\Import\Message\DeleteImportedCsvMessage;
use App\Service\Import\MessageHandler\DeleteImportedCsvMessageHandler;
use App\Service\Media\MediaService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberImportFactory;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteImportedCsvMessageHandler::class)]
#[CoversClass(ClearWorkerMemoryEventListener::class)]
class DeleteImportedCsvMessageHandlerTest extends KernelTestCase
{
    public function test_delete_imported_csv(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $content = 'Hello World';

        $mediaService = $this->container->get(MediaService::class);
        assert($mediaService instanceof MediaService);

        $filesystem = $this->container->get(Filesystem::class);
        assert($filesystem instanceof Filesystem);

        $oldMedia = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'folder' => MediaFolder::IMPORT,
            'created_at' => new \DateTimeImmutable('-8 days'),
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($oldMedia),
            $content,
        );

        $oldPendingMedia = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'folder' => MediaFolder::IMPORT,
            'created_at' => new \DateTimeImmutable('-8 days'),
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($oldPendingMedia),
            $content,
        );

        $oldFailedMedia = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'folder' => MediaFolder::IMPORT,
            'created_at' => new \DateTimeImmutable('-8 days'),
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($oldFailedMedia),
            $content,
        );

        $recentMedia = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'folder' => MediaFolder::IMPORT,
            'created_at' => new \DateTimeImmutable('-2 days'),
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($recentMedia),
            $content,
        );

        $recentPendingMedia = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'folder' => MediaFolder::IMPORT,
            'created_at' => new \DateTimeImmutable('-2 days'),
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($recentPendingMedia),
            $content,
        );

        $recentFailedMedia = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'folder' => MediaFolder::IMPORT,
            'created_at' => new \DateTimeImmutable('-2 days'),
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($recentFailedMedia),
            $content,
        );

        $oldOtherFolderMedia = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'folder' => MediaFolder::NEWSLETTER_IMAGES,
            'created_at' => new \DateTimeImmutable('-10 days'),
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($oldOtherFolderMedia),
            $content,
        );

        $recentOtherFolderMedia = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'folder' => MediaFolder::NEWSLETTER_IMAGES,
            'created_at' => new \DateTimeImmutable('-2 days'),
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($recentOtherFolderMedia),
            $content,
        );


        SubscriberImportFactory::createOne([
            'media' => $oldMedia,
            'status' => SubscriberImportStatus::COMPLETED,
            'created_at' => new \DateTimeImmutable('-8 days'),
        ]);
        SubscriberImportFactory::createOne([
            'media' => $oldPendingMedia,
            'status' => SubscriberImportStatus::REQUIRES_INPUT,
            'created_at' => new \DateTimeImmutable('-8 days'),
        ]);
        SubscriberImportFactory::createOne([
            'media' => $oldFailedMedia,
            'status' => SubscriberImportStatus::FAILED,
            'created_at' => new \DateTimeImmutable('-8 days'),
        ]);
        SubscriberImportFactory::createOne([
            'media' => $recentMedia,
            'status' => SubscriberImportStatus::COMPLETED,
            'created_at' => new \DateTimeImmutable('-2 days'),
        ]);
        SubscriberImportFactory::createOne([
            'media' => $recentPendingMedia,
            'status' => SubscriberImportStatus::REQUIRES_INPUT,
            'created_at' => new \DateTimeImmutable('-2 days'),
        ]);
        SubscriberImportFactory::createOne([
            'media' => $recentFailedMedia,
            'status' => SubscriberImportStatus::FAILED,
            'created_at' => new \DateTimeImmutable('-2 days'),
        ]);


        $transport = $this->transport('scheduler_default');
        $transport->send(new DeleteImportedCsvMessage());
        $this->em->clear();
        $transport->throwExceptions()->process();

        $this->assertNull($mediaService->getMediaByUuid($oldMedia->getUuid()));
        $this->assertNotNull($mediaService->getMediaByUuid($oldPendingMedia->getUuid()));
        $this->assertNull($mediaService->getMediaByUuid($oldFailedMedia->getUuid()));
        $this->assertNotNull($mediaService->getMediaByUuid($recentMedia->getUuid()));
        $this->assertNotNull($mediaService->getMediaByUuid($recentPendingMedia->getUuid()));
        $this->assertNotNull($mediaService->getMediaByUuid($recentFailedMedia->getUuid()));
        $this->assertNotNull($mediaService->getMediaByUuid($oldOtherFolderMedia->getUuid()));
        $this->assertNotNull($mediaService->getMediaByUuid($recentOtherFolderMedia->getUuid()));


        $this->assertFalse($filesystem->fileExists($mediaService->getUploadPath($oldMedia)));
        $this->assertTrue($filesystem->fileExists($mediaService->getUploadPath($oldPendingMedia)));
        $this->assertFalse($filesystem->fileExists($mediaService->getUploadPath($oldFailedMedia)));
        $this->assertTrue($filesystem->fileExists($mediaService->getUploadPath($recentMedia)));
        $this->assertTrue($filesystem->fileExists($mediaService->getUploadPath($recentPendingMedia)));
        $this->assertTrue($filesystem->fileExists($mediaService->getUploadPath($recentFailedMedia)));
        $this->assertTrue($filesystem->fileExists($mediaService->getUploadPath($oldOtherFolderMedia)));
        $this->assertTrue($filesystem->fileExists($mediaService->getUploadPath($recentOtherFolderMedia)));
    }
}
