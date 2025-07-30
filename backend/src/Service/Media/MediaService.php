<?php

namespace App\Service\Media;

use App\Entity\Media;
use App\Entity\Newsletter;
use App\Entity\Type\MediaFolder;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Uid\Uuid;

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
        Newsletter $newsletter,
        MediaFolder $folder,
        UploadedFile $file,
    ): Media {
        $uuid = Uuid::v4();
        $originalName = $file->getClientOriginalName();
        $extension = $file->getExtension();

        if (empty($extension)) {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        }

        if (empty($extension)) {
            throw new MediaUploadException('Unable to determine file extension');
        }

        // create media entity
        $media = new Media();
        $media->setUuid($uuid);
        $media->setNewsletter($newsletter);
        $media->setCreatedAt(new \DateTimeImmutable());
        $media->setUpdatedAt(new \DateTimeImmutable());
        $media->setFolder($folder);
        $media->setExtension($extension);
        $media->setSize($file->getSize());
        $media->setOriginalName($originalName);
        $media->setIsPrivate($folder->isPrivate());

        $stream = fopen($file->getPathname(), 'r+');
        if ($stream === false) {
            throw new MediaUploadException('Unable to open file stream');
        }

        try {
            $this->filesystem->writeStream(
                $this->getUploadPath($media),
                $stream
            );
        } catch (FilesystemException $e) {
            throw new MediaUploadException('Unable to upload file', previous: $e);
        } finally {
            fclose($stream);
        }

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    public function delete(Media $media): void
    {
        $path = $this->getUploadPath($media);
        try {
            $this->filesystem->delete($path);
        } catch (FilesystemException $e) {
            throw new MediaDeleteException('Unable to delete media', previous: $e);
        }

        $this->em->remove($media);
        $this->em->flush();
    }

    public function getUploadPath(Media $media): string
    {
        return sprintf(
            '%s/%s/%s.%s',
            $media->getNewsletter()->getId(),
            $media->getFolder()->value,
            $media->getUuid(),
            $media->getExtension()
        );
    }

    public function getPublicUrl(Media $media): string
    {
        $componentUrl = $this->instanceUrlResolver->publicUrlOf($this->internalConfig->getComponent());

        return sprintf(
            '%s/api/public/media/%s.%s',
            $componentUrl,
            $media->getUuid(),
            $media->getExtension()
        );
    }

    public function getMediaByUuid(string $uuid): ?Media
    {
        return $this->em
            ->getRepository(Media::class)
            ->findOneBy([
                'uuid' => $uuid,
            ]);
    }

    /**
     * @return resource
     * @throws MediaReadException
     */
    public function getMediaStream(Media $media)
    {
        $uploadPath = $this->getUploadPath($media);
        try {
            return $this->filesystem->readStream($uploadPath);
        } catch (FilesystemException $e) {
            throw new MediaReadException('Unable to read media stream', previous: $e);
        }
    }

    public function getMimeType(string $extension): string
    {
        $mime = new MimeTypes();
        $mimeTypes = $mime->getMimeTypes($extension);

        if (count($mimeTypes) === 0) {
            return 'application/octet-stream';
        }

        return $mimeTypes[0];
    }

}
