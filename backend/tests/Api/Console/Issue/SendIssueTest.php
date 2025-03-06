<?php

namespace Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Service\Issue\SendService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
#[CoversClass(IssueRepository::class)]
#[CoversClass(Issue::class)]
#[CoversClass(IssueObject::class)]
#[CoverClass(SendService::class)]
class SendIssueTest extends WebTestCase
{
    // Input validation tests
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

        $subscriber1 = SubscriberFactory::createOne(['project' => $project]);

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
}
