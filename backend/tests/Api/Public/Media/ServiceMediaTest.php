<?php

namespace App\Tests\Api\Public\Media;

use App\Api\Public\Controller\Media\MediaController;
use App\Service\Media\MediaService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\MediaFactory;
use App\Tests\Factory\NewsletterFactory;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Uid\Uuid;

#[CoversClass(MediaController::class)]
#[CoversClass(MediaService::class)]
class ServiceMediaTest extends WebTestCase
{

    public function test_fails_when_media_not_found(): void
    {
        $uuid = Uuid::v4();
        $this->publicApi(
            'GET',
            '/media/' . $uuid . '.txt',
        );

        $this->assertResponseStatusCodeSame(404);
        $json = $this->getJson();
        $this->assertSame('Media not found', $json['message']);
    }

    public function test_fails_when_media_extension_does_not_match(): void
    {
        $media = MediaFactory::createOne([
            'extension' => 'jpg',
        ]);

        $this->publicApi(
            'GET',
            '/media/' . $media->getUuid() . '.txt',
        );

        $this->assertResponseStatusCodeSame(404);
        $json = $this->getJson();
        $this->assertSame('Media not found.', $json['message']);
    }

    public function test_when_private(): void
    {
        $project = NewsletterFactory::createOne();
        $media = MediaFactory::createOne([
            'is_private' => true,
            'extension' => 'txt',
        ]);

        $this->publicApi(
            'GET',
            '/media/' . $media->getUuid() . '.txt',
        );

        $this->assertResponseStatusCodeSame(403);
        $json = $this->getJson();
        $this->assertSame('Not authorized to access this media', $json['message']);
    }

    public function test_serves_media(): void
    {
        $uuid = Uuid::v4();
        $project = NewsletterFactory::createOne();

        $filesystem = $this->container->get(Filesystem::class);
        assert($filesystem instanceof Filesystem);

        $mediaService = $this->container->get(MediaService::class);
        assert($mediaService instanceof MediaService);

        $content = 'Hello World';
        $media = MediaFactory::createOne([
            'project' => $project,
            'uuid' => $uuid,
            'extension' => 'txt',
            'size' => strlen($content),
        ]);

        $filesystem->write(
            $mediaService->getUploadPath($media),
            $content,
        );

        $response = $this->publicApi(
            'GET',
            '/media/' . $uuid . '.txt',
        );
        $this->assertInstanceOf(StreamedResponse::class, $response);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertResponseHeaderSame('Content-Length', (string)$media->getSize());
        $this->assertResponseHeaderSame('Cache-Control', 'max-age=31536000, public');
        $this->assertSame($content, $this->client->getInternalResponse()->getContent());
    }

}