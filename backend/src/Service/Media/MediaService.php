<?php

namespace App\Service\Media;

use App\Entity\Media;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaService
{

    public function __construct(
        private Filesystem $filesystem,
        private EntityManagerInterface $em,
        private InternalConfig $internalConfig,
        private InstanceUrlResolver $instanceUrlResolver
    ) {
    }

    /**
     * @throws MediaUploadException
     */
    public function upload(
        Project $project,
        MediaUploadTypeEnum $type,
        UploadedFile $file,
    ): Media {
        // upload file
        $folder = $type->getUploadFolder();

        $originalName = $file->getClientOriginalName();
        $originalNameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);

        $extension = $file->guessExtension();

        if ($extension === null) {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        }

        if (empty($extension)) {
            throw new MediaUploadException('Unable to determine file extension');
        }

        $filepath = sprintf(
            '%s/%s.%s',
            $folder,
            $originalNameWithoutExtension . '-' . uniqid(),
            $extension
        );

        $stream = fopen($file->getPathname(), 'r+');

        if ($stream === false) {
            throw new MediaUploadException('Unable to open file stream');
        }

        try {
            $this->filesystem->writeStream(
                $this->getUploadPath($project, $filepath),
                $stream
            );
        } catch (FilesystemException $e) {
            throw new MediaUploadException('Unable to upload file', previous: $e);
        } finally {
            fclose($stream);
        }

        // create media entity
        $media = new Media();
        $media->setProject($project);
        $media->setCreatedAt(new \DateTimeImmutable());
        $media->setUpdatedAt(new \DateTimeImmutable());
        $media->setType($type->value);
        $media->setPath($filepath);
        $media->setSize($file->getSize());
        $media->setExtension($extension);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    private function getUploadPath(Project $project, string $path): string
    {
        return sprintf(
            '%s/%s',
            $project->getId(),
            $path
        );
    }

    public function getMediaUrlFromPath(Project $project, string $path): string
    {
        $componentUrl = $this->instanceUrlResolver->publicUrlOf($this->internalConfig->getComponent());

        return sprintf(
            '%s/api/public/media/%s/%s',
            $componentUrl,
            $project->getUuid(),
            $path
        );
    }

}