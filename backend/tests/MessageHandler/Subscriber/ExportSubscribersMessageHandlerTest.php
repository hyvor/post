<?php

namespace App\Tests\MessageHandler\Subscriber;

use App\Entity\Newsletter;
use App\Entity\Type\MediaFolder;
use App\Service\Media\MediaService;
use App\Service\Subscriber\Message\ExportSubscribersMessage;
use App\Service\Subscriber\MessageHandler\ExportSubscribersMessageHandler;
use App\Service\Subscriber\SubscriberCsvExporter;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(ExportSubscribersMessageHandler::class)]
#[CoversClass(ExportSubscribersMessage::class)]
class ExportSubscribersMessageHandlerTest extends KernelTestCase
{
    public function test_export_subscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();

        // Create some subscribers
        SubscriberFactory::createMany(3, [
            'newsletter' => $newsletter,
        ]);

        // Create some metadata definitions
        SubscriberMetadataDefinitionFactory::createMany(2, [
            'newsletter' => $newsletter,
        ]);

        $message = new ExportSubscribersMessage($newsletter->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport()->throwExceptions()->process();

        // Verify the file was created and uploaded
        $mediaService = $this->container->get(MediaService::class);
        $mediaFiles = $mediaService->list($newsletter, MediaFolder::EXPORT);

        $this->assertCount(1, $mediaFiles);
        $this->assertSame('subscribers.csv', $mediaFiles[0]->getName());
        $this->assertSame('text/csv', $mediaFiles[0]->getMimeType());
    }

    public function test_export_subscribers_with_no_subscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $message = new ExportSubscribersMessage($newsletter->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport()->throwExceptions()->process();

        // Verify the file was created and uploaded even with no subscribers
        $mediaService = $this->container->get(MediaService::class);
        $mediaFiles = $mediaService->list($newsletter, MediaFolder::IMPORT);

        $this->assertCount(1, $mediaFiles);
        $this->assertSame('subscribers.csv', $mediaFiles[0]->getName());
        $this->assertSame('text/csv', $mediaFiles[0]->getMimeType());
    }
}
