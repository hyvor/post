<?php

namespace App\Command\Dev;

use App\Entity\Type\UserRole;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
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
        private KernelInterface $kernel
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

        $project = ProjectFactory::createOne([
            'uuid' => 'c9cb3415-eb28-4a43-932c-550675675852',
            'name' => 'Test Project',
        ]);

        SubscriberMetadataDefinitionFactory::createOne([
            'project' => $project,
            'key' => 'name',
            'name' => 'Name',
        ]);

        $user = UserFactory::createOne([
            'hyvor_user_id' => 1,
            'project' => $project,
            'role' => UserRole::OWNER
        ]);

        $list1 = NewsletterListFactory::createOne([
            'project' => $project,
            'name' => 'PHP',
            'description' => 'Get the latest PHP news'
        ]);
        $list2 = NewsletterListFactory::createOne([
            'project' => $project,
            'name' => 'Typescript',
            'description' => 'Get the latest Typescript news'
        ]);

        $output->writeln('<info>Database seeded with test data.</info>');

        return Command::SUCCESS;
    }

}