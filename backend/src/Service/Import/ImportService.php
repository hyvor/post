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
     * @return string[]
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

        return array_values(array_filter($headers, fn($h) => $h !== null));
    }

    /**
     * @param array<int, string>|null $csv_fields
     */
    public function createSubscriberImport(Media $media, ?array $csv_fields = null): SubscriberImport
    {
        $subscriberImport = new SubscriberImport();
        $subscriberImport->setNewsletter($media->getNewsletter());
        $subscriberImport->setMedia($media);
        $subscriberImport->setCsvFields($csv_fields);
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

    /**
     * @return SubscriberImport[]
     */
    public function getSubscriberImports(Newsletter $newsletter, int $limit = 30, int $offset = 0): array
    {
        $qb = $this->em->getRepository(SubscriberImport::class)
            ->createQueryBuilder('si')
            ->where('si.newsletter = :newsletter')
            ->setParameter('newsletter', $newsletter)
            ->orderBy('si.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        /** @var SubscriberImport[] $results */
        $results = $qb->getQuery()->getResult();

        return $results;
    }
}
