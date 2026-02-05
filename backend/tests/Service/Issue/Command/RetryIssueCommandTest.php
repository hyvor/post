<?php

namespace Service\Issue\Command;

use App\Service\Issue\Command\RetryIssueCommand;
use App\Service\Issue\Message\SendIssueMessage;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(RetryIssueCommand::class)]
class RetryIssueCommandTest extends KernelTestCase {

    public function test_retries(): void
    {

        $this->assertNotNull(self::$kernel);
        $application = new Application(self::$kernel);
        $command = $application->find('issue:retry');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'id' =>  1,
            '--pagination-size' => 10
        ]);

        $messages = $this->transport('async')->queue()->all();
        $this->assertCount(1, $messages);

        $message = $messages[0]->getMessage();
        $this->assertInstanceOf(SendIssueMessage::class, $message);
        $this->assertSame(1, $message->getIssueId());
        $this->assertSame(10, $message->getPaginationSize());
    }

}
