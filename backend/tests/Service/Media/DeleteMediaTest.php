<?php

namespace App\Tests\Service\Media;

use App\Service\Media\MediaService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\NewsletterFactory;
use League\Flysystem\Filesystem;
use Symfony\Component\Uid\Uuid;

class DeleteMediaTest extends KernelTestCase
{
    public function test_delete_media(): void
    {
        $uuid1 = Uuid::v4();
        $uuid2 = Uuid::v4();
        $uuid3 = Uuid::v4();

        $newsletter = NewsletterFactory::createOne();

        $filesystem = $this->container->get(Filesystem::class);
        assert($filesystem instanceof Filesystem);

        $mediaService = $this->container->get(MediaService::class);
        assert($mediaService instanceof MediaService);

        $content = 'Hello World';

        $media1 = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'uuid' => $uuid1,
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($media1),
            $content,
        );

        $media2 = MediaFactory::createOne([
            'newsletter' => $newsletter,
            'uuid' => $uuid2,
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($media2),
            $content,
        );

        $media3 = MediaFactory::createOne([
            'newsletter' => NewsletterFactory::createOne(),
            'uuid' => $uuid3,
            'extension' => 'txt',
            'size' => strlen($content),
        ]);
        $filesystem->write(
            $mediaService->getUploadPath($media3),
            $content,
        );

        $mediaService->delete($media1->_real());

        $this->assertFalse($filesystem->fileExists($mediaService->getUploadPath($media1)));
        $this->assertNull($mediaService->getMediaByUuid($uuid1));

        $this->assertTrue($filesystem->fileExists($mediaService->getUploadPath($media2)));
        $this->assertNotNull($mediaService->getMediaByUuid($uuid2));

        $this->assertTrue($filesystem->fileExists($mediaService->getUploadPath($media3)));
        $this->assertNotNull($mediaService->getMediaByUuid($uuid3));
    }
}
