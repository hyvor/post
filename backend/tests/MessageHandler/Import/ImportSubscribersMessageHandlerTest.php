<?php

namespace App\Tests\MessageHandler\Import;

use App\Entity\Subscriber;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Import\MessageHandler\ImportSubscribersMessageHandler;
use App\Service\Media\MediaService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SubscriberImportFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(ImportSubscribersMessageHandler::class)]
#[CoversClass(ImportSubscribersMessage::class)]
class ImportSubscribersMessageHandlerTest extends KernelTestCase
{
    public function test_import_subscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $file = new UploadedFile(
            dirname(__DIR__, 2) . '/Service/Import/import.csv',
            'import.csv',
            mimeType: "text/csv",
        );

        /** @var MediaService $mediaService */
        $mediaService = $this->container->get(MediaService::class);
        $media = $mediaService->upload(
            $newsletter->_real(),
            MediaFolder::IMPORT,
            $file,
        );

        NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
            'name' => 'List 1',
        ]);

        NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
            'name' => 'List 2',
        ]);

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'media' => $media,
            'status' => SubscriberImportStatus::IMPORTING,
            'fields' => [
                "email" => "email",
                "lists" => "lists"
            ],
        ]);

        $message = new ImportSubscribersMessage($subscriberImport->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport('async')->throwExceptions()->process();

        $importedSubscribers = $this->em->getRepository(Subscriber::class)->findBy(['newsletter' => $newsletter->_real()]);

        $this->assertCount(3, $importedSubscribers);
        $this->assertSame('john@hyvor.com', $importedSubscribers[0]->getEmail());
        $this->assertSame(2, count($importedSubscribers[0]->getLists()));
        $this->assertSame('jane@hyvor.com', $importedSubscribers[1]->getEmail());
        $this->assertSame(1, count($importedSubscribers[1]->getLists()));
        $this->assertSame('doe@hyvor.com', $importedSubscribers[2]->getEmail());
        $this->assertSame(1, count($importedSubscribers[2]->getLists()));
    }
}
