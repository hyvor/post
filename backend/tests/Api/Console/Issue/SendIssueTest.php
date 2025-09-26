<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Service\Issue\Message\SendIssueMessage;
use App\Service\Issue\SendService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SendingProfileFactory;
use App\Tests\Factory\SubscriberFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\BillingInterface;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\InternalConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
#[CoversClass(IssueRepository::class)]
#[CoversClass(Issue::class)]
#[CoversClass(IssueObject::class)]
#[CoversClass(SendService::class)]
class SendIssueTest extends WebTestCase
{
    // Input validation tests
    // TODO: Refactor validation test into one
    public function testSendNonDraftIssue(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENT
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );
        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Issue is not a draft.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::SENT, $issue->getStatus());
    }

    public function testSendIssueWithoutSubject(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
            'subject' => null
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Subject cannot be empty.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
    }

    public function testSendIssueWithoutList(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
            'listIds' => []
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Issue must have at least one list.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
    }

    public function testSendIssueWithoutContent(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
            'list_ids' => [$list->getId()],
            'content' => null
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Content cannot be empty.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
    }

    public function testSendIssueWithoutSubscribers(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
            'list_ids' => [$list1->getId()],
            'content' => 'content'
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('No subscribers to send to.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
    }

    public function testSendIssueUpdate(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
            'lists' => [$list]
        ]);

        $sendingProfile = SendingProfileFactory::createOne([
            'from_email' => 'newsletter@hyvor.com',
            'from_name' => 'Hyvor Newsletter',
            'reply_to_email' => 'no-reply@hyvor.com',
        ]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
            'list_ids' => [$list->getId()],
            'sending_profile' => $sendingProfile,
        ]);

        $internalConfig = $this->getContainer()->get(InternalConfig::class);
        $licence = new PostLicense(emails: 10);

        $billing = new BillingFake($internalConfig, license: $licence);

        $this->getContainer()->set(BillingInterface::class, $billing);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame($issue->getId(), $json['id']);
        $this->assertSame('sending', $json['status']);
        $this->assertSame(new \DateTimeImmutable()->getTimestamp(), $json['sending_at']);

        $issueRepository = $this->em->getRepository(Issue::class);
        $issue = $issueRepository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::SENDING, $issue->getStatus());
        $this->assertSame(new \DateTimeImmutable()->format('Y-m-d'), $issue->getSendingAt()?->format('Y-m-d'));
        $this->assertSame('newsletter@hyvor.com', $issue->getFromEmail());
        $this->assertSame('Hyvor Newsletter', $issue->getFromName());
        $this->assertSame('no-reply@hyvor.com', $issue->getReplyToEmail());

        $transport = $this->transport('async');
        $transport->queue()->assertCount(1);
        $message = $transport->queue()->first()->getMessage();
        $this->assertInstanceOf(SendIssueMessage::class, $message);

        $transport->throwExceptions()->process(1);

        $sendRepository = $this->em->getRepository(Send::class);
        $send = $sendRepository->findOneBy([
            'issue' => $issue->getId(),
            'subscriber' => $subscriber->getId(),
        ]);
        $this->assertNotNull($send);
        $issueDB = $issueRepository->find($send->getIssue()->getId());
        $this->assertNotNull($issueDB);
        $this->assertInstanceOf(Issue::class, $issueDB);
        $this->assertSame($issueDB->getTotalSends(), 1);
    }

    public function test_send_issue_rate_limit(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
            'lists' => [$list]
        ]);

        $issueSent = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::SENT,
            'list_ids' => [$list->getId()],
            'content' => "content"
        ]);

        $sends = SendFactory::createMany(10, [
            'status' => SendStatus::SENT,
            'issue' => $issueSent,
            'newsletter' => $newsletter,
            'created_at' => new \DateTimeImmutable(),
        ]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
            'list_ids' => [$list->getId()],
            'content' => "content"
        ]);

        $internalConfig = $this->getContainer()->get(InternalConfig::class);
        $licence = new PostLicense(emails: 10);

        $billing = new BillingFake($internalConfig, license: $licence);

        $this->getContainer()->set(BillingInterface::class, $billing);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('would_exceed_limit', $json['message']);
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
        $this->assertSame(10, $json['data']['limit']);
        $this->assertSame(1, $json['data']['exceed_amount']);
    }

}
