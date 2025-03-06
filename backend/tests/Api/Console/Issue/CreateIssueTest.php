<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
#[CoversClass(IssueRepository::class)]
#[CoversClass(Issue::class)]
#[CoversClass(IssueObject::class)]
class CreateIssueTest extends WebTestCase
{
    public function testCreateIssueDraft(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $response = $this->consoleApi(
            $project,
            'POST',
            '/issues',
            []
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame('draft', $json['status']);
        $this->assertSame([$list->getId()], $json['lists']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
        $this->assertSame([$list->getId()], $issue->getListids());
        $this->assertSame('2025-02-21 00:00:00', $issue->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertSame($project->getId(), $issue->getProject()->getId());
    }
}
