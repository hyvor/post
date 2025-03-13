<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SubscriberStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Service\Issue\Message\IssueSendMessage;
use App\Service\Issue\SendService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
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
        $project = ProjectFactory::createOne();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'status' => IssueStatus::SENT
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );
        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Issue is not a draft.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::SENT, $issue->getStatus());
    }

    public function testSendIssueWithoutSubject(): void
    {
        $project = ProjectFactory::createOne();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'status' => IssueStatus::DRAFT,
            'subject' => null
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Subject cannot be empty.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
    }

    public function testSendIssueWithoutList(): void
    {
        $project = ProjectFactory::createOne();

        $issue = IssueFactory::createOne([
            'project' => $project,
            'status' => IssueStatus::DRAFT,
            'listIds' => []
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Issue must have at least one list.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
    }

    public function testSendIssueWithoutContent(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'status' => IssueStatus::DRAFT,
            'list_ids' => [$list->getId()],
            'content' => null
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Content cannot be empty.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
    }

    public function testSendIssueWithoutSubscribers(): void
    {
        $project = ProjectFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => $project]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'status' => IssueStatus::DRAFT,
            'list_ids' => [$list1->getId()],
            'content' => 'content'
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('No subscribers to send to.', $json['message']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
    }

    public function testSendIssueUpdate(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $subscriber = SubscriberFactory::createOne([
            'project' => $project,
            'status' => SubscriberStatus::SUBSCRIBED,
            'lists' => [$list]
        ]);

        $issue = IssueFactory::createOne([
            'project' => $project,
            'status' => IssueStatus::DRAFT,
            'list_ids' => [$list->getId()],
            'content' => "content"
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            "/issues/" . $issue->getId() . "/send"
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertSame($issue->getId(), $json['id']);
        $this->assertSame('sending', $json['status']);
        $this->assertSame(new \DateTimeImmutable()->getTimestamp(), $json['sending_at']);

        $issueRepository = $this->em->getRepository(Issue::class);
        $issue = $issueRepository->find($issue->getId());
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::SENDING, $issue->getStatus());
        $this->assertSame(new \DateTimeImmutable()->format('Y-m-d'), $issue->getSendingAt()?->format('Y-m-d'));

        $this->transport()->queue()->assertCount(1);
        $message = $this->transport()->queue()->first()->getMessage();
        $this->assertInstanceOf(IssueSendMessage::class, $message);

        $this->transport()->throwExceptions()->process();

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
}
