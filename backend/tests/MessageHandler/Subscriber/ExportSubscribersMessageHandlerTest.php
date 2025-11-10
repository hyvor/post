<?php

namespace App\Tests\MessageHandler\Subscriber;

use App\Entity\Media;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberStatus;
use App\Service\Subscriber\Message\ExportSubscribersMessage;
use App\Service\Subscriber\MessageHandler\ExportSubscribersMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberExportFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use Illuminate\Support\Str;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ExportSubscribersMessageHandler::class)]
#[CoversClass(ExportSubscribersMessage::class)]
class ExportSubscribersMessageHandlerTest extends KernelTestCase
{
    public function test_export_subscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $metadata = SubscriberMetadataDefinitionFactory::createMany(2, [
            'newsletter' => $newsletter,
        ]);
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
            'metadata' => [
                $metadata[0]->getKey() => Str::random(5),
                $metadata[1]->getKey() => Str::random(5),
            ],
        ]);
        SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
            'metadata' => [
                $metadata[0]->getKey() => Str::random(5),
                $metadata[1]->getKey() => Str::random(5),
            ],
        ]);
        SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::UNSUBSCRIBED,
            'metadata' => [
                $metadata[0]->getKey() => Str::random(5),
                $metadata[1]->getKey() => Str::random(5),
            ],
        ]);

        $export = SubscriberExportFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $message = new ExportSubscribersMessage($export->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport('async')->throwExceptions()->process();

        $media = $this->em->getRepository(Media::class)->findBy([
            'newsletter' => $newsletter->_real(),
            'folder' => MediaFolder::EXPORT,
        ]);
        $this->assertCount(1, $media);
        $this->assertSame('subscribers.csv', $media[0]->getOriginalName());
        $this->assertSame('csv', $media[0]->getExtension());

        $filesystem = $this->container->get(Filesystem::class);
        assert($filesystem instanceof Filesystem);

        $read = $filesystem->read(
            $newsletter->getId() . '/' .
            MediaFolder::EXPORT->value . '/' .
            $media[0]->getUuid() . '.' . $media[0]->getExtension()
        );

        // Headers
        $this->assertStringContainsString("Email,Status,\"Subscribed At\",Source,\"{$metadata[0]->getKey()}\",\"{$metadata[1]->getKey()}\"", $read);

        // Subscriber rows
        $subscriberMetadata = $subscriber->getMetadata();
        $this->assertStringContainsString(
            "{$subscriber->getEmail()},{$subscriber->getStatus()->value},\"{$subscriber->getSubscribedAt()?->format('Y-m-d H:i:s')}\",{$subscriber->getSource()->value},{$subscriberMetadata[$metadata[0]->getKey()]},{$subscriberMetadata[$metadata[1]->getKey()]}",
            $read
        );
    }

    public function test_export_subscribers_with_no_subscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $export = SubscriberExportFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $message = new ExportSubscribersMessage($export->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport('async')->throwExceptions()->process();

        // Verify the file was created and uploaded even with no subscribers
        $media = $this->em->getRepository(Media::class)->findBy([
            'newsletter' => $newsletter->_real(),
            'folder' => MediaFolder::EXPORT,
        ]);
        $this->assertCount(1, $media);
        $this->assertSame('subscribers.csv', $media[0]->getOriginalName());
        $this->assertSame('csv', $media[0]->getExtension());

        $filesystem = $this->container->get(Filesystem::class);
        assert($filesystem instanceof Filesystem);
        $read = $filesystem->read(
            $newsletter->getId() . '/' .
            MediaFolder::EXPORT->value . '/' .
            $media[0]->getUuid() . '.' . $media[0]->getExtension()
        );

        // Only default headers should be present
        $this->assertSame("Email,Status,\"Subscribed At\",Source\n", $read);

    }
}
