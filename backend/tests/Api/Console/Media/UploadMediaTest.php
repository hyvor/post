<?php

namespace App\Tests\Api\Console\Media;

use App\Api\Console\Controller\MediaController;
use App\Api\Console\Object\MediaObject;
use App\Entity\Media;
use App\Entity\Type\MediaFolder;
use App\Service\Media\MediaService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(MediaController::class)]
#[CoversClass(MediaService::class)]
#[CoversClass(MediaObject::class)]
class UploadMediaTest extends WebTestCase
{

    public function test_upload_invalid_file_when_importing(): void
    {
        $this->markTestSkipped();

        // @phpstan-ignore-next-line
        $newsletter = NewsletterFactory::createOne();

        $file = new UploadedFile(
            __DIR__ . '/upload_test.css',
            'upload_test.css',
        );

        $this->consoleApi(
            $newsletter,
            'POST',
            '/media',
            files: [
                'file' => $file,
            ],
            parameters: [
                'folder' => 'issue_images',
            ]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertHasViolation('', 'The extension of the file is invalid ("css")');
    }

    public function test_large_file(): void
    {
        $this->markTestSkipped();

        // @phpstan-ignore-next-line
        $filePath = sys_get_temp_dir() . '/large_test_file.jpg';

        // total 110MB
        for ($i = 0; $i < 11; $i++) {
            file_put_contents($filePath, str_repeat('a', 1024 * 1024 * 10), FILE_APPEND); // 10MB
        }

        $file = new UploadedFile(
            $filePath,
            'large_test_file.jpg',
        );

        $newsletter = NewsletterFactory::createOne();
        $this->consoleApi(
            $newsletter,
            'POST',
            '/media',
            files: [
                'file' => $file,
            ],
            parameters: [
                'folder' => 'issue_images',
            ]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertHasViolation('', 'The file is too large');

        unlink($filePath);
    }

    public function test_uploads_csv_file(): void
    {
        $this->markTestSkipped();

        // @phpstan-ignore-next-line
        $file = new UploadedFile(
            __DIR__ . '/import.csv',
            'import.csv',
            mimeType: "text/csv",
        );

        $newsletter = NewsletterFactory::createOne();
        $this->consoleApi(
            $newsletter,
            'POST',
            '/media',
            files: [
                'file' => $file,
            ],
            parameters: [
                'folder' => 'import',
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $json = $this->getJson();
        $this->assertSame('import', $json['folder']);

        $url = $json['url'];
        $this->assertIsString($url);
        $this->assertStringStartsWith(
            'https://post.hyvor.com/api/public/media/',
            $url
        );
        $this->assertStringEndsWith('.csv', $url);

        $this->assertSame(135, $json['size']);
        $this->assertSame('csv', $json['extension']);

        $entity = $this->em->getRepository(Media::class)->find($json['id']);
        $this->assertInstanceOf(Media::class, $entity);
        $this->assertSame($newsletter->getId(), $entity->getNewsletter()->getId());
        $this->assertSame(MediaFolder::IMPORT, $entity->getFolder());
        $this->assertSame('csv', $entity->getExtension());
        $this->assertSame(135, $entity->getSize());

        // uploaded file
        $filesystem = $this->container->get(Filesystem::class);
        assert($filesystem instanceof Filesystem);

        $read = $filesystem->read(
            $newsletter->getId() . '/' .
            $entity->getFolder()->value . '/' .
            $entity->getUuid() . '.' . $entity->getExtension()
        );
        $this->assertStringContainsString('ID,Name,Department,Salary', $read);
    }
}