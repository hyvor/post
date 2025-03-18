<?php

namespace App\Tests\MessageHandler\Issue;

use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Issue\Message\SendIssueMessage;
use App\Service\Issue\MessageHandler\SendIssueMessageHandler;
use App\Service\Issue\SendService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[CoversClass(SendIssueMessageHandler::class)]
#[CoversClass(SendService::class)]
class SendIssueMessageHandlerTest extends KernelTestCase
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

        $message = new SendIssueMessage($issue->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport()->throwExceptions()->process(1);

        $allMessages = $this->transport()->queue()->all();
        $this->assertCount(5, $allMessages);

        // by default, the max emails per second value is 1
        $first = $allMessages[0];
        $this->assertSame(0, $first->envelope->last(DelayStamp::class)?->getDelay());
        $second = $allMessages[1];
        $this->assertSame(1000, $second->envelope->last(DelayStamp::class)?->getDelay());

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->findOneBy([
            'issue' => $issue->getId(),
            'subscriber' => $subscribers[0]->getId(),
        ]);
        $this->assertNotNull($send);
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame($issue->getId(), $send->getIssue()->getId());
        $this->assertSame($subscribers[0]->getId(), $send->getSubscriber()->getId());
    }
}
