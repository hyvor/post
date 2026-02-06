<?php

namespace App\Service\Issue\Command;

use App\Service\Issue\Message\SendIssueMessage;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'issue:retry',
    description: 'Retries an issue (partially run or failed one)',
)]
class RetryIssueCommand {

    public function __construct(
        private MessageBusInterface $bus,
    )
    {}

    public function __invoke(
        OutputInterface $output,
        #[Argument] int $id,
        #[Option] int $paginationSize = SendIssueMessage::PAGINATION_SIZE,
    ): int
    {

        $this->bus->dispatch(new SendIssueMessage($id, $paginationSize));
        $output->writeln('<info>Issue retry message dispatched successfully.</info>');

        return Command::SUCCESS;
    }

}
