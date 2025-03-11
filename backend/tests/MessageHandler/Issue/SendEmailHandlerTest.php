<?php

namespace App\Tests\MessageHandler\Issue;

use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Issue\Message\IssueSendMessage;
use App\Service\Issue\MessageHandler\IssueSendMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueSendMessageHandler::class)]
class SendEmailHandlerTest extends KernelTestCase
{

    public function test_send_email(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'project' => $project,
        ]);

        $subscribers = SubscriberFactory::createMany(5, [
            'project' => $project,
            'lists' => [$list],
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
        ]);

        $message = new IssueSendMessage($issue);
        $this->getMessageBus()->dispatch($message);

        $this->transport()->process();

        $sendRepository = $this->em->getRepository(Send::class);
        dd($sendRepository->findAll());
        $send = $sendRepository->find(['issue' => $issue, 'subscriber' => $subscribers[0]]);
        $this->assertNotNull($send);
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame($issue, $send->getIssue());
        $this->assertSame($subscribers[0], $send->getSubscriber());
    }
}
