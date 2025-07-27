<?php

namespace App\Tests\Service\Import;

use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use App\Entity\Type\SubscriberStatus;
use App\Service\Import\Dto\ImportingSubscriberDto;
use App\Service\Import\Parser\CsvParser;
use App\Service\Import\Parser\ParserAbstract;
use App\Service\Media\MediaService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberImportFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(CsvParser::class)]
#[CoversClass(ParserAbstract::class)]
class CsvParserTest extends KernelTestCase
{
    public function test_parse(): void
    {
        $parser = $this->container->get(CsvParser::class);
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

        SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
            'key' => 'name',
            'type' => SubscriberMetadataDefinitionType::TEXT,
        ]);
        SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
            'key' => 'address',
            'type' => SubscriberMetadataDefinitionType::TEXT,
        ]);

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'media' => $media,
            'status' => SubscriberImportStatus::IMPORTING,
            'fields' => [
                "email" => "email",
                "lists" => "lists",
                'subscribed_at' => 'subscribed at',
                'subscribe_ip' => 'subscribe ip',
                'name' => 'name',
                'address' => 'address',
            ],
        ]);

        $subscribers = $parser->parse($subscriberImport);

        $this->assertCount(3, $subscribers);
        $this->assertInstanceOf(ImportingSubscriberDto::class, $subscribers[0]);
        $this->assertSame('john@hyvor.com', $subscribers[0]->email);
        $this->assertSame([1,2], $subscribers[0]->lists);
        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscribers[0]->status);
        $this->assertSame('2024-10-03 10:45:00', $subscribers[0]->subscribedAt?->format('Y-m-d H:i:s'));
        $this->assertSame('192.168.1.1', $subscribers[0]->subscribeIp);
        $this->assertSame(['name' => 'John', 'address' => 'john_addr'], $subscribers[0]->metadata);
    }
}
