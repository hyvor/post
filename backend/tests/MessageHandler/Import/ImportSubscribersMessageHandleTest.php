<?php

namespace App\Tests\MessageHandler\Import;

use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Import\MessageHandler\ImportSubscribersMessageHandler;
use App\Service\Media\MediaService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberImportFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(ImportSubscribersMessageHandler::class)]
#[CoversClass(ImportSubscribersMessage::class)]
class ImportSubscribersMessageHandleTest extends KernelTestCase
{
    public function test_import_subscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $file = new UploadedFile(
            __DIR__ . '/import.csv',
            'import.csv',
            mimeType: "text/csv",
        );

        $mediaService = $this->container->get(MediaService::class);
        $media = $mediaService->upload(
            $newsletter->_real(),
            MediaFolder::IMPORT,
            $file,
        );

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'media' => $media,
            'status' => SubscriberImportStatus::IMPORTING,
            'fields' => [
                "name" => 'name',
                "email" => "email",
                "lists" => "lists"
            ],
        ]);

        $message = new ImportSubscribersMessage($subscriberImport->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport()->throwExceptions()->process();



    }
}
