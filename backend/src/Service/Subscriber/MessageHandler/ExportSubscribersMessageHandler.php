<?php

namespace App\Service\Subscriber\MessageHandler;

use App\Entity\Newsletter;
use App\Entity\Type\MediaFolder;
use App\Service\Media\MediaService;
use App\Service\Subscriber\Message\ExportSubscribersMessage;
use App\Service\Subscriber\SubscriberCsvExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ExportSubscribersMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private SubscriberCsvExporter $csvExporter,
        private MediaService $mediaService,
    ) {
    }

    public function __invoke(ExportSubscribersMessage $message): void
    {
        $newsletter = $this->em->getRepository(Newsletter::class)->find($message->getNewsletterId());
        assert($newsletter !== null);

        $csvPath = $this->csvExporter->createFile($newsletter);

        $file = new UploadedFile(
            $csvPath,
            'subscribers.csv',
            'text/csv',
            null,
            true
        );

        $this->mediaService->upload(
            $newsletter,
            MediaFolder::EXPORT,
            $file,
        );

        // Clean up the temporary file
        unlink($csvPath);
    }
}
