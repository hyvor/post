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
        private MediaService           $mediaService,
        private EntityManagerInterface $em,
    )
    {
    }

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

    public function getRowCount(Media $media): int
    {
        $stream = $this->mediaService->getMediaStream($media);

        if (!is_resource($stream)) {
            throw new ImportException("Invalid CSV stream.");
        }

        $rowCount = 0;
        while (fgetcsv($stream) !== false) {
            $rowCount++;
        }
        fclose($stream);

        return max(0, $rowCount - 1);
    }

    /**
     * @param array<int, string>|null $csvFields
     */
    public function createSubscriberImport(
        Media  $media,
        string $source,
        ?array $csvFields = null,
        ?int   $csvRows = null
    ): SubscriberImport
    {
        $subscriberImport = new SubscriberImport();
        $subscriberImport->setNewsletter($media->getNewsletter());
        $subscriberImport->setMedia($media);
        $subscriberImport->setCsvFields($csvFields);
        $subscriberImport->setCsvRows($csvRows);
        $subscriberImport->setStatus(SubscriberImportStatus::REQUIRES_INPUT);
        $subscriberImport->setSource($source);
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
    public function getSubscriberImports(
        ?Newsletter             $newsletter = null,
        ?SubscriberImportStatus $status = null,
        int                     $limit = 30,
        int                     $offset = 0
    ): array
    {
        $qb = $this->em->getRepository(SubscriberImport::class)
            ->createQueryBuilder('si');

        if ($newsletter !== null) {
            $qb->where('si.newsletter = :newsletter')
                ->setParameter('newsletter', $newsletter);
        }

        if ($status !== null) {
            $qb->andWhere('si.status = :status')
                ->setParameter('status', $status);
        }

        $qb->orderBy('si.created_at', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        /** @var SubscriberImport[] $results */
        $results = $qb->getQuery()->getResult();

        return $results;
    }

    /**
     * @param Newsletter $newsletter
     * @return array{
     *     day: int,
     *     month: int
     * }
     */
    public function getNewsletterImportCounts(Newsletter $newsletter): array
    {
        $qb = $this->em->getRepository(SubscriberImport::class)
            ->createQueryBuilder('si')
            ->select('SUM(CASE WHEN si.created_at >= :start_of_day THEN 1 ELSE 0 END) AS day_count,
                SUM(CASE WHEN si.created_at >= :start_of_month THEN 1 ELSE 0 END) AS month_count')
            ->where('si.newsletter = :newsletter')
            ->andWhere('si.status = :status')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('status', SubscriberImportStatus::COMPLETED)
            ->setParameter('start_of_day', $this->now()->modify('today'))
            ->setParameter('start_of_month', $this->now()->modify('first day of this month midnight'));

        /** @var array{day_count: int|null, month_count: int|null} $result */
        $result = $qb->getQuery()->getSingleResult();

        return [
            'day' => $result['day_count'] ?? 0,
            'month' => $result['month_count'] ?? 0
        ];
    }
}
