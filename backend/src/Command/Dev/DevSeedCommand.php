<?php

namespace App\Command\Dev;

use App\Entity\Type\IssueStatus;
use App\Entity\Type\SubscriberStatus;
use App\Entity\Type\UserRole;
use App\Service\Content\ContentService;
use App\Service\EmailTemplate\EmailTemplateService;
use App\Service\EmailTemplate\HtmlEmailTemplateRenderer;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\SubscriberMetadataDefinitionFactory;
use App\Tests\Factory\UserFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(
    name: 'app:dev:seed',
    description: 'Seeds the database with test data for development purposes.'
)]
class DevSeedCommand extends Command
{

    public function __construct(
        private KernelInterface $kernel,
        private ContentService $contentService,
        private EmailTemplateService $emailTemplateService,
        private HtmlEmailTemplateRenderer $htmlEmailTemplateRenderer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $env = $this->kernel->getEnvironment();
        if ($env !== 'dev' && $env !== 'test') {
            $output->writeln('<error>This command can only be run in the dev and test environments.</error>');
            return Command::FAILURE;
        }

        $newsletter = NewsletterFactory::createOne([
            'uuid' => 'c9cb3415-eb28-4a43-932c-550675675852',
            'name' => 'Test Newsletter',
            'slug' => 'test'
        ]);

        SubscriberMetadataDefinitionFactory::createOne([
            'newsletter' => $newsletter,
            'key' => 'name',
            'name' => 'Name',
        ]);

        $user = UserFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
            'role' => UserRole::OWNER
        ]);

        $list1 = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
            'name' => 'PHP',
            'description' => 'Get the latest PHP news'
        ]);
        $list2 = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
            'name' => 'Typescript',
            'description' => 'Get the latest Typescript news'
        ]);

        SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'email' => 'supun@hyvor.com',
            'lists' => [$list1, $list2],
            'status' => SubscriberStatus::SUBSCRIBED
        ]);
        SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'email' => 'ishini@hyvor.com',
            'lists' => [$list1, $list2],
            'status' => SubscriberStatus::SUBSCRIBED
        ]);

        $issue = IssueFactory::createOne([
            'subject' => 'Content Style Guide',
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENT,
            'content' => $this->contentService->getJsonFromHtml($this->contentService->getDefaultContentStyleHtml())
        ]);
        $issue->setHtml($this->htmlEmailTemplateRenderer->renderFromIssue($issue));

        DomainFactory::createOne([
            'user_id' => 1,
            'domain' => 'example.com',
            'verified_in_ses' => true
        ]);

        $output->writeln('<info>Database seeded with test data.</info>');

        return Command::SUCCESS;
    }

}