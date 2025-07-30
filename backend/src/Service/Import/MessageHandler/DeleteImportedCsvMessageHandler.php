<?php

namespace App\Service\Import\MessageHandler;

use App\Entity\Media;
use App\Entity\SubscriberImport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Message\DeleteImportedCsvMessage;
use App\Service\Media\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteImportedCsvMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MediaService $mediaService,
    ) {
    }

    public function __invoke(DeleteImportedCsvMessage $message): void
    {
        $media = $this->em->createQueryBuilder()
            ->select('m')
            ->from(Media::class, 'm')
            ->innerJoin(SubscriberImport::class, 'si', 'WITH', 'si.media = m')
            ->where('m.folder = :folder')
            ->andWhere('si.status != :status')
            ->andWhere('si.created_at < :date')
            ->setParameter('folder', MediaFolder::IMPORT)
            ->setParameter('status', SubscriberImportStatus::REQUIRES_INPUT)
            ->setParameter('date', new \DateTimeImmutable('-7 days'))
            ->getQuery()
            ->getResult();

        assert(is_iterable($media));

        foreach ($media as $item) {
            $this->mediaService->delete($item);
        }
    }
}
