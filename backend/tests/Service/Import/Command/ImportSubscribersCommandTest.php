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
            "b@hyvor.com,\"List 1, Unknown\",Bob\n" .
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
            '--id' => $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--lists-column' => 'Tags',
            '--preview' => true,
        ]);

        $this->assertSame(Command::SUCCESS, $code);
        $display = $tester->getDisplay();
        $this->assertStringContainsString('PREVIEW', $display);
        $this->assertStringContainsString($newsletter->getSubdomain(), $display);
        $this->assertStringContainsString('Available lists', $display);
        $this->assertStringContainsString('List 1 (#', $display);
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
            '--subdomain' => $newsletter->getSubdomain(),
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

    public function test_forced_lists(): void
    {
        $newsletter = NewsletterFactory::createOne();
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 1']);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 2']);
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 3']);

        $tester = $this->commandTester('app:subscribers:import');
        $code = $tester->execute([
            '--id' => $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--lists-column' => 'Tags',
            '--list' => ['List 3', (string) $list2->getId()],
            '--yes' => true,
        ], ['interactive' => false]);
        $this->assertSame(Command::SUCCESS, $code, $tester->getDisplay());

        $listsOf = function (string $email): array {
            $sub = $this->em->getRepository(Subscriber::class)->findOneBy(['email' => $email]);
            $this->assertNotNull($sub);
            $names = array_map(fn($l) => $l->getName(), $sub->getLists()->toArray());
            sort($names);
            return $names;
        };

        $this->assertSame(['List 1', 'List 2', 'List 3'], $listsOf('a@hyvor.com'));
        $this->assertSame(['List 1', 'List 2', 'List 3'], $listsOf('b@hyvor.com'));
        // no lists in file: only the forced lists (not all lists)
        $this->assertSame(['List 2', 'List 3'], $listsOf('c@hyvor.com'));
    }

    public function test_preview_shows_effective_lists(): void
    {
        $newsletter = NewsletterFactory::createOne();
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 1']);
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 2']);
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 3']);

        $tester = $this->commandTester('app:subscribers:import');
        $tester->execute([
            '--id' => $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--lists-column' => 'Tags',
            '--list' => ['List 3'],
            '--preview' => true,
        ]);

        $display = $tester->getDisplay();
        $this->assertMatchesRegularExpression('/a@hyvor\.com\s+List 1, List 2, List 3 \(forced\)\s/', $display);
        // "Unknown" is not a list of the newsletter, so it is dropped; List 3 is forced
        $this->assertMatchesRegularExpression('/b@hyvor\.com\s+List 1, List 3 \(forced\)\s/', $display);
        // no lists in file: only the forced list
        $this->assertMatchesRegularExpression('/c@hyvor\.com\s+List 3 \(forced\)\s/', $display);
    }

    public function test_delimiter(): void
    {
        $this->file = $this->file . '.sep.csv';
        file_put_contents($this->file, "Email;Tags\na@hyvor.com;List 1, List 2\nb@hyvor.com;List 2\n");

        $newsletter = NewsletterFactory::createOne();
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 1']);
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 2']);
        NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'List 3']);

        $options = [
            '--id' => $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--lists-column' => 'Tags',
        ];
        $tester = $this->commandTester('app:subscribers:import');

        // wrong (default) separator: columns are not found
        $this->assertSame(Command::FAILURE, $tester->execute($options));
        $this->assertStringContainsString("Column 'Email'", $tester->getDisplay());

        // preview with the right separator
        $this->assertSame(Command::SUCCESS, $tester->execute($options + ['--delimiter' => ';', '--preview' => true]));
        $this->assertStringContainsString('a@hyvor.com', $tester->getDisplay());

        $code = $tester->execute($options + ['--delimiter' => ';', '--yes' => true], ['interactive' => false]);
        $this->assertSame(Command::SUCCESS, $code, $tester->getDisplay());

        $sub = $this->em->getRepository(Subscriber::class)->findOneBy(['email' => 'a@hyvor.com']);
        $this->assertNotNull($sub);
        $names = array_map(fn($l) => $l->getName(), $sub->getLists()->toArray());
        sort($names);
        $this->assertSame(['List 1', 'List 2'], $names);

        $this->assertSame(Command::FAILURE, $tester->execute($options + ['--delimiter' => ';;']));
        $this->assertStringContainsString('--delimiter must be a single character', $tester->getDisplay());
        $this->assertSame(Command::FAILURE, $tester->execute($options + ['--delimiter' => '']));
        @unlink($this->file);
    }

    public function test_unknown_forced_list(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $tester = $this->commandTester('app:subscribers:import');
        $code = $tester->execute([
            '--id' => $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--list' => ['Nope'],
        ]);
        $this->assertSame(Command::FAILURE, $code);
        $this->assertStringContainsString("List 'Nope' not found", $tester->getDisplay());
    }

    public function test_errors(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $tester = $this->commandTester('app:subscribers:import');

        $this->assertSame(Command::FAILURE, $tester->execute(['--id' => 999999, 'file' => $this->file]));
        $this->assertStringContainsString('not found', $tester->getDisplay());

        $this->assertSame(Command::FAILURE, $tester->execute(['--id' => $newsletter->getId(), 'file' => '/nope.csv']));
        $this->assertStringContainsString('does not exist', $tester->getDisplay());

        // default email column "email" does not exist (file has "Email")
        $this->assertSame(Command::FAILURE, $tester->execute(['--id' => $newsletter->getId(), 'file' => $this->file]));
        $this->assertStringContainsString("Column 'email'", $tester->getDisplay());

        $this->assertSame(Command::FAILURE, $tester->execute([
            '--id' => $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--metadata' => ['unknown=Name'],
        ]));
        $this->assertStringContainsString('not defined', $tester->getDisplay());

        $this->assertSame(Command::FAILURE, $tester->execute([
            '--id' => $newsletter->getId(),
            'file' => $this->file,
            '--email-column' => 'Email',
            '--metadata' => ['bad'],
        ]));
        $this->assertStringContainsString('Use key=column', $tester->getDisplay());

        // neither / both of --id and --subdomain
        $this->assertSame(Command::FAILURE, $tester->execute(['file' => $this->file]));
        $this->assertStringContainsString('exactly one of --id or --subdomain', $tester->getDisplay());
        $this->assertSame(Command::FAILURE, $tester->execute([
            'file' => $this->file,
            '--id' => $newsletter->getId(),
            '--subdomain' => $newsletter->getSubdomain(),
        ]));
        $this->assertStringContainsString('exactly one of --id or --subdomain', $tester->getDisplay());
    }
}
