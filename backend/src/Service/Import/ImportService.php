<?php

namespace App\Service\Import;

use App\Entity\Media;
use App\Entity\Newsletter;
use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Dto\UpdateSubscriberImportDto;
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

        return $headers;
    }

    public function createSubscriberImport(Media $media): SubscriberImport
    {
        $subscriberImport = new SubscriberImport();
        $subscriberImport->setNewsletter($media->getNewsletter());
        $subscriberImport->setMedia($media);
        $subscriberImport->setStatus(SubscriberImportStatus::REQUIRES_INPUT);
        $subscriberImport->setCreatedAt($this->now());
        $subscriberImport->setUpdatedAt($this->now());

        $this->em->persist($subscriberImport);
        $this->em->flush();

        return $subscriberImport;
    }

    public function updateSubscriberImport(SubscriberImport $subscriberImport, UpdateSubscriberImportDto $updates): SubscriberImport
    {
        if ($updates->hasProperty('status')) {
            $subscriberImport->setStatus($updates->status);
        }
        if ($updates->hasProperty('fields')) {
            $subscriberImport->setFields($updates->fields);
        }
        if ($updates->hasProperty('errorMessage')) {
            $subscriberImport->setErrorMessage($updates->errorMessage);
        }
        $subscriberImport->setUpdatedAt($this->now());

        $this->em->persist($subscriberImport);
        $this->em->flush();

        return $subscriberImport;
    }

    public function getPendingImportOfNewsletter(Newsletter $newsletter, int $id): ?SubscriberImport
    {
        return $this->em
            ->getRepository(SubscriberImport::class)
            ->findOneBy([
                'id' => $id,
                'newsletter' => $newsletter,
                'status' => SubscriberImportStatus::REQUIRES_INPUT,
            ]);
    }
}
