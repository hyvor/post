<?php

namespace App\Service\Media;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class FilesystemFactory
{

    /**
     * @param 's3' | 'local' $adapterType
     */
    public static function create(
        string $adapterType,
        S3Client $s3Client,
        ?string $bucket,
        string $uploadDir
    ): Filesystem {
        if ($adapterType === 's3') {
            assert(is_string($bucket));
            $adapter = new AwsS3V3Adapter($s3Client, $bucket);
        } else {
            $adapter = new LocalFilesystemAdapter($uploadDir);
        }

        return new Filesystem($adapter);
    }

}