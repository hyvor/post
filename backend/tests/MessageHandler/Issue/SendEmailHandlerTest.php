<?php

namespace App\Tests\MessageHandler\Issue;

use App\Entity\Send;
use App\Entity\Type\SubscriberStatus;
use App\Service\Issue\Message\IssueSendMessage;
use App\Service\Issue\MessageHandler\IssueSendMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueSendMessageHandler::class)]
class SendEmailHandlerTest extends KernelTestCase
{

    public function test_send_email(): void
    {
        $issue = IssueFactory::createOne();
        $message = new IssueSendMessage($issue);
        $this->getMessageBus()->dispatch($message);

        $this->transport()->process();

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->findAll();
        dd($send);
        $this->assertNotNull($send);
    }

}
