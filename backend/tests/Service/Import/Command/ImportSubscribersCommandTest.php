<?php

namespace App\Tests\Service\Import\Command;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use App\Service\Import\Command\ImportSubscribersCommand;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;

#[CoversClass(ImportSubscribersCommand::class)]
class ImportSubscribersCommandTest extends KernelTestCase
{
    private string $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = tempnam(sys_get_temp_dir(), 'import') ?: '';
        file_put_contents(
            $this->file,
            "Email,Tags,Name\n" .
            "a@hyvor.com,\"List 1, List 2\",Alice\n" .
            "b@hyvor.com,List 1; Unknown,Bob\n" .
            "not-an-email,List 1,Eve\n" .
            "c@hyvor.com,,Carl\n"
        );
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    private function subscriberCount(int $newsletterId): int
    {
        return $this->em->getRepository(Subscriber::class)->count(['newsletter' => $newsletterId]);
    }

    public function test_preview_makes_no_changes(): void
    {
        $newsletter = NewsletterFactory::createOne();
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 1']);
        SubscriberFactory::createOne(['newsletter' => $newsletter, 'email' => 'a@hyvor.com']);

        $tester = $this->commandTester('app:subscribers:import');
        $code = $tester->execute([
            'newsletter' => (string) $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--lists-column' => 'Tags',
            '--preview' => true,
        ]);

        $this->assertSame(Command::SUCCESS, $code);
        $display = $tester->getDisplay();
        $this->assertStringContainsString('PREVIEW', $display);
        $this->assertStringContainsString('Invalid email: not-an-email', $display);
        $this->assertStringContainsString('Unknown', $display);
        $this->assertStringContainsString('Already subscribed (skipped)', $display);
        $this->assertStringContainsString('No changes were made', $display);
        $this->assertSame(1, $this->subscriberCount($newsletter->getId()));
    }

    public function test_imports(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 1']);
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 2']);
        SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
            'key' => 'name',
            'type' => SubscriberMetadataDefinitionType::TEXT,
        ]);

        $tester = $this->commandTester('app:subscribers:import');
        $code = $tester->execute([
            'newsletter' => $newsletter->getSubdomain(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--lists-column' => 'Tags',
            '--metadata' => ['name=Name'],
            '--yes' => true,
        ], ['interactive' => false]);

        $this->assertSame(Command::SUCCESS, $code, $tester->getDisplay());
        $this->assertCount(3, $this->em->getRepository(Subscriber::class)->findBy(['newsletter' => $newsletter->getId()]));

        $a = $this->em->getRepository(Subscriber::class)->findOneBy(['email' => 'a@hyvor.com']);
        $this->assertNotNull($a);
        $this->assertSame(['name' => 'Alice'], $a->getMetadata());
    }

    public function test_errors(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $tester = $this->commandTester('app:subscribers:import');

        $this->assertSame(Command::FAILURE, $tester->execute(['newsletter' => '999999', 'file' => $this->file]));
        $this->assertStringContainsString('not found', $tester->getDisplay());

        $this->assertSame(Command::FAILURE, $tester->execute(['newsletter' => (string) $newsletter->getId(), 'file' => '/nope.csv']));
        $this->assertStringContainsString('does not exist', $tester->getDisplay());

        // default email column "email" does not exist (file has "Email")
        $this->assertSame(Command::FAILURE, $tester->execute(['newsletter' => (string) $newsletter->getId(), 'file' => $this->file]));
        $this->assertStringContainsString("Column 'email'", $tester->getDisplay());

        $this->assertSame(Command::FAILURE, $tester->execute([
            'newsletter' => (string) $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--metadata' => ['unknown=Name'],
        ]));
        $this->assertStringContainsString('not defined', $tester->getDisplay());

        $this->assertSame(Command::FAILURE, $tester->execute([
            'newsletter' => (string) $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--metadata' => ['bad'],
        ]));
        $this->assertStringContainsString('Use key=column', $tester->getDisplay());
    }
}
