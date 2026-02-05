<?php

namespace App\Tests\MessageHandler\Issue;

use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Service\Issue\Message\SendIssueMessage;
use App\Service\Issue\MessageHandler\SendIssueMessageHandler;
use App\Service\Issue\SendService;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[CoversClass(SendIssueMessageHandler::class)]
#[CoversClass(SendService::class)]
#[CoversClass(SendIssueMessage::class)]
class SendIssueMessageHandlerTest extends KernelTestCase
{

    public function test_send_email(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscribers = SubscriberFactory::createMany(5, [
            'newsletter' => $newsletter,
            'lists' => [$list],
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
        ]);

        $message = new SendIssueMessage($issue->getId());
        $this->getMessageBus()->dispatch($message);

        $this->transport('async')->throwExceptions()->process(1);

        $allMessages = $this->transport('async')->queue()->all();
        $this->assertCount(5, $allMessages);

        // by default, the max emails per second value is 8
        $first = $allMessages[0];
        $this->assertSame(0, $first->envelope->last(DelayStamp::class)?->getDelay());
        $second = $allMessages[1];
        $this->assertSame(125, $second->envelope->last(DelayStamp::class)?->getDelay());

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->findOneBy([
            'issue' => $issue->getId(),
            'subscriber' => $subscribers[0]->getId(),
        ]);
        $this->assertNotNull($send);
        $this->assertInstanceOf(Send::class, $send);
        $this->assertSame($issue->getId(), $send->getIssue()->getId());
        $this->assertSame($subscribers[0]->getId(), $send->getSubscriber()->getId());

        $this->assertSame(IssueStatus::SENT, $issue->getStatus());
    }

    public function test_handle_on_issue_id_subscriber_id_conflict(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscribers = SubscriberFactory::createMany(3, [
            'newsletter' => $newsletter,
            'lists' => [$list],
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
        ]);

        SendFactory::createOne([
            'issue' => $issue,
            'subscriber' => $subscribers[0],
            'newsletter' => $newsletter,
            'status' => SendStatus::PENDING
        ]);

        $message = new SendIssueMessage($issue->getId());
        $this->getMessageBus()->dispatch($message);

        $this->em->clear();
        $this->transport('async')->throwExceptions()->process(1);

        $allMessages = $this->transport('async')->queue()->all();
        // New messages should be dispatched only to 2 subscribers
        $this->assertCount(2, $allMessages);

        $sendRepository = $this->em->getRepository(Send::class);
        $sends = $sendRepository->findBy([
            'issue' => $issue->getId(),
            'newsletter' => $newsletter->getId(),
        ]);
        $this->assertCount(3, $sends);
    }

    public function test_paginates(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscribers = SubscriberFactory::createMany(3, [
            'newsletter' => $newsletter,
            'lists' => [$list],
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'listIds' => [$list->getId()],
            'status' => IssueStatus::SENDING,
        ]);

        $transport = $this->transport('async')->throwExceptions();
        $transport->send(new SendIssueMessage($issue->getId(), paginationSize: 2));
        $transport->processOrFail(1);

        $messages = $transport->queue()->all();
        $this->assertCount(3, $messages);
    }
}
