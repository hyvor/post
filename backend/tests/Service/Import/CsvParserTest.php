<?php

namespace App\Tests\Service\Import;

use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Import\Dto\ImportingSubscriberDto;
use App\Service\Import\Parser\CsvParser;
use App\Service\Import\Parser\ParserAbstract;
use App\Service\Media\MediaService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberImportFactory;
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

        $subscriberImport = SubscriberImportFactory::createOne([
            'newsletter' => $newsletter,
            'media' => $media,
            'status' => SubscriberImportStatus::IMPORTING,
            'fields' => [
                "email" => "email",
                "lists" => "lists",
                'subscribed_at' => 'subscribed_at',
                'subscribe_ip' => 'subscribe_ip',
            ],
        ]);

        $subscribers = $parser->parse($subscriberImport);

        $this->assertCount(3, $subscribers, 'Expected 3 subscribers to be parsed');
        $this->assertInstanceOf(ImportingSubscriberDto::class, $subscribers[0], 'Subscribers should be a ImportingSubscriberDto');;
        $this->assertSame('john@hyvor.com', $subscribers[0]->email, 'Subscriber email should match');
        $this->assertSame([1,2], $subscribers[0]->lists, 'Subscriber lists should match');
        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscribers[0]->status, 'Subscriber status should match');
        // TODO: Subscribed at and subscribe IP assertions
    }

}
