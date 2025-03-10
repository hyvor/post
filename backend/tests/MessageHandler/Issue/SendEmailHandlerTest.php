<?php

namespace App\Tests\MessageHandler\Issue;

use App\Service\Issue\Message\SendEmailMessage;
use App\Service\Issue\MessageHandler\SendEmailHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SendEmailHandler::class)]
class SendEmailHandlerTest extends KernelTestCase
{

    public function test_send_email(): void
    {

        $issue = IssueFactory::createOne();
        $message = new SendEmailMessage($issue);
        $this->getMessageBus()->dispatch($message);

        $this->transport()->process();

        // assertions

    }

}