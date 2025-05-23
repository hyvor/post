<?php

namespace App\Service\Subscriber\MessageHandler;

use App\Entity\Newsletter;
use App\Entity\SubscriberExport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberExportStatus;
use App\Service\Media\MediaService;
use App\Service\Subscriber\Message\ExportSubscribersMessage;
use App\Service\Subscriber\SubscriberCsvExporter;
use App\Service\Subscriber\SubscriberService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportSubscribersMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberService $subscriberService,
        private SubscriberCsvExporter $csvExporter,
        private MediaService $mediaService,
    ) {
    }

    public function __invoke(ExportSubscribersMessage $message): void
    {
        $subscriberExport = $this->em->getRepository(SubscriberExport::class)->find($message->getSubscriberExportId());
        assert($subscriberExport !== null);

        $newsletter = $subscriberExport->getNewsletter();

        $csvPath = $this->csvExporter->createFile($newsletter);

        $file = new UploadedFile(
            $csvPath,
            'subscribers.csv',
            'text/csv',
            null,
            true
        );

        try {
            $media = $this->mediaService->upload(
                $newsletter,
                MediaFolder::EXPORT,
                $file,
            );

            $this->subscriberService->markSubscriberExportAsCompleted($subscriberExport, $media);
        }
        catch (\Exception $e) {
            $this->subscriberService->markSubscriberExportAsFailed($subscriberExport, $e->getMessage());
        }

        // Clean up the temporary file
        unlink($csvPath);




    }
}
