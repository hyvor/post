<?php

namespace App\Tests\Api\Console\Media;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadMediaTest extends WebTestCase
{

    public function test_upload_invalid_file(): void
    {
        $project = ProjectFactory::createOne();

        $file = new UploadedFile(
            __DIR__ . '/upload_test.css',
            'upload_test.css',
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            '/media',
            files: [
                'file' => $file,
            ]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertHasViolation('file', 'The extension of the file is invalid ("css")');
    }

    public function test_large_file(): void
    {
        $filePath = sys_get_temp_dir() . '/large_test_file.jpg';

        // total 110MB
        for ($i = 0; $i < 11; $i++) {
            file_put_contents($filePath, str_repeat('a', 1024 * 1024 * 10), FILE_APPEND); // 10MB
        }

        $file = new UploadedFile(
            $filePath,
            'large_test_file.jpg',
        );

        $project = ProjectFactory::createOne();
        $response = $this->consoleApi(
            $project,
            'POST',
            '/media',
            files: [
                'file' => $file,
            ]
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertHasViolation('file', 'The file is too large');
    }

}