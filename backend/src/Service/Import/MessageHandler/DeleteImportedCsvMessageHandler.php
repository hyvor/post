<?php

namespace App\Service\Import\MessageHandler;

use App\Entity\Media;
use App\Entity\SubscriberImport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Message\DeleteImportedCsvMessage;
use App\Service\Media\MediaDeleteException;
use App\Service\Media\MediaService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteImportedCsvMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MediaService $mediaService,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(DeleteImportedCsvMessage $message): void
    {

        /** @var array<Media> $media */
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

        foreach ($media as $item) {
            try {
                $this->mediaService->delete($item);
            } catch (MediaDeleteException) {
                $this->logger->error('Failed to delete media', [
                    'media_id' => $item->getId(),
                ]);
            }
        }
    }
}
