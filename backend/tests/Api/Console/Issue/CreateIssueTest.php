<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendingProfileFactory;
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

        $newsletter = NewsletterFactory::createOne([
            'slug' => 'thibault'
        ]);

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/issues',
            []
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame('draft', $json['status']);
        $this->assertSame([$list->getId()], $json['lists']);
        $this->assertSame('thibault@hvrpst.com', $json['from_email']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
        $this->assertSame([$list->getId()], $issue->getListids());
        $this->assertSame('2025-02-21 00:00:00', $issue->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertSame($newsletter->getId(), $issue->getNewsletter()->getId());
        $this->assertSame('thibault@hvrpst.com', $issue->getFromEmail());
    }

    public function test_create_issue_draft_with_custom_email(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne();

        $domain = DomainFactory::createOne(
            [
                'domain' => 'hyvor.com',
                'verified_in_ses' => true,
                'user_id' => 1
            ]
        );

        $sendingEmail = SendingProfileFactory::createOne([
            'email' => 'thibault@hyvor.com',
            'newsletter' => $newsletter,
            'domain' => $domain,
            'is_default' => true
        ]);

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/issues',
            []
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame('draft', $json['status']);
        $this->assertSame([$list->getId()], $json['lists']);
        $this->assertSame('thibault@hyvor.com', $json['from_email']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
        $this->assertSame([$list->getId()], $issue->getListids());
        $this->assertSame('2025-02-21 00:00:00', $issue->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertSame($newsletter->getId(), $issue->getNewsletter()->getId());
        $this->assertSame('thibault@hyvor.com', $issue->getFromEmail());
    }
}
