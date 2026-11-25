<?php

namespace App\Tests\Service\Import;

use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use App\Entity\Type\SubscriberStatus;
use App\Service\Import\Dto\ImportingSubscriberDto;
use App\Service\Import\Parser\CsvParser;
use App\Service\Import\Parser\ParserAbstract;
use App\Service\Import\Parser\ParserFactory;
use App\Service\Media\MediaService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SubscriberImportFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[CoversClass(CsvParser::class)]
#[CoversClass(ParserAbstract::class)]
class CsvParserTest extends KernelTestCase
{
    public function test_parse(): void
    {
        /** @var ParserFactory $parserFactory */
        $parserFactory = $this->container->get(ParserFactory::class);
        $parser = $parserFactory->csv();

        $newsletter = NewsletterFactory::createOne();

        $file = new UploadedFile(
            __DIR__ . '/import.csv',
            'import.csv',
            mimeType: "text/csv",
        );

        /** @var MediaService $mediaService */
        $mediaService = $this->container->get(MediaService::class);
        $media = $mediaService->upload(
            $newsletter,
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
                'metadata_name' => 'name',
                'metadata_address' => 'address',
            ],
        ]);

        $subscribers = $parser->parse($subscriberImport);

        $this->assertCount(3, $subscribers);
        $this->assertInstanceOf(ImportingSubscriberDto::class, $subscribers[0]);
        $this->assertSame('john@hyvor.com', $subscribers[0]->email);
        $this->assertSame(["List 1", "List 2", "List 3"], $subscribers[0]->lists);
        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscribers[0]->status);
        $this->assertSame('2024-10-03 10:45:00', $subscribers[0]->subscribedAt?->format('Y-m-d H:i:s'));
        $this->assertSame('192.168.1.1', $subscribers[0]->subscribeIp);
        $this->assertSame(['name' => 'John', 'address' => 'john_addr'], $subscribers[0]->metadata);
    }

    /**
     * @return array<string, array{string, string[]}>
     */
    public static function listsProvider(): array
    {
        return [
            'json' => ['["List 1", "List 2"]', ['List 1', 'List 2']],
            'comma (mailchimp)' => ['List 1, List 2', ['List 1', 'List 2']],
            'semicolon (buttondown)' => ['List 1; List 2', ['List 1', 'List 2']],
            'single' => ['List 1', ['List 1']],
            'empty' => ['', []],
            'whitespace and empty items' => [' List 1 ,, ;List 2; ', ['List 1', 'List 2']],
            'duplicates' => ['A,B,A', ['A', 'B']],
            'invalid json' => ['[List 1', []],
            'json with non-strings' => ['["A", 1, null]', ['A']],
        ];
    }

    /**
     * @param string[] $expected
     */
    #[DataProvider('listsProvider')]
    public function test_parse_lists(string $input, array $expected): void
    {
        $this->assertSame($expected, CsvParser::parseLists($input));
    }

    public function test_parse_with_comma_and_semicolon_lists(): void
    {
        /** @var ParserFactory $parserFactory */
        $parserFactory = $this->container->get(ParserFactory::class);
        $parser = $parserFactory->csv();
        $newsletter = NewsletterFactory::createOne();

        $csv = "email,lists\n" .
            "a@hyvor.com,\"List 1, List 2\"\n" .
            "b@hyvor.com,List 1; List 2\n" .
            "c@hyvor.com,\n";
        $path = tempnam(sys_get_temp_dir(), 'import');
        file_put_contents($path, $csv);

        $stream = fopen($path, 'r');
        $this->assertIsResource($stream);
        $subscribers = $parser->parseStream($stream, $newsletter, ['email' => 'email', 'lists' => 'lists'])->toArray();
        unlink($path);

        $this->assertCount(3, $subscribers);
        $this->assertSame(['List 1', 'List 2'], $subscribers[0]->lists);
        $this->assertSame(['List 1', 'List 2'], $subscribers[1]->lists);
        $this->assertSame([], $subscribers[2]->lists);
    }
}
