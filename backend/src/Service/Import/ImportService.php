<?php

namespace App\Service\Import;

use App\Entity\Media;
use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Media\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class ImportService
{
    use ClockAwareTrait;

    public function __construct(
        private MediaService $mediaService,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @return array<int, string>
     */
    public function getFields(Media $media): array
    {
        $stream = $this->mediaService->getMediaStream($media);

        if (!is_resource($stream)) {
            throw new ImportException("Invalid CSV stream.");
        }

        $headers = fgetcsv($stream);
        fclose($stream);

        if ($headers === false) {
            throw new ImportException("Could not read CSV headers.");
        }

        $subscriberImport = new SubscriberImport();
        $subscriberImport->setNewsletter($media->getNewsletter());
        $subscriberImport->setMedia($media);
        $subscriberImport->setStatus(SubscriberImportStatus::REQUIRES_INPUT);
        $subscriberImport->setCreatedAt($this->now());
        $subscriberImport->setUpdatedAt($this->now());

        $this->em->persist($subscriberImport);
        $this->em->flush();

        return $headers;
    }

}
