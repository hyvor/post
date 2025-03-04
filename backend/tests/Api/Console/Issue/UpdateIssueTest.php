<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Issue;
use App\Entity\Project;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(IssueController::class)]
#[CoversClass(IssueService::class)]
#[CoversClass(IssueRepository::class)]
#[CoversClass(Issue::class)]
class UpdateIssueTest extends WebTestCase
{

    // TODO: tests for authentication


    public function updateIssueStatus(string $status, string $dateField): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $expectedDate = new \DateTimeImmutable('2025-02-21');

        $project = ProjectFactory::createOne();
        $list = NewsletterListFactory::createOne(['project' => $project]);
        $issue = IssueFactory::createOne(['project' => $project, 'status' => IssueStatus::DRAFT, 'lists' => [$list]]);

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'status' => $status,
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame($status, $json['status']);
        $this->assertSame($expectedDate->getTimestamp(), $json[$dateField]);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame(constant(IssueStatus::class . '::' . strtoupper($status)), $issue->getStatus());

        // Convert date field to camelCase method
        $getterMethod = 'get' . str_replace('_', '', ucwords($dateField, '_'));
        $this->assertSame('2025-02-21 00:00:00', $issue->$getterMethod()?->format('Y-m-d H:i:s'));
    }

    #[TestWith(['sending', 'sending_at'])]
    #[TestWith(['sent', 'sent_at'])]
    public function testUpdateIssueStatuses(string $status, string $dateField): void
    {
        $this->updateIssueStatus($status, $dateField);
    }


    public function testUpdateIssueAllFields(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $issue = IssueFactory::createOne(['project' => $project, 'lists' => [$list]]);

        $scheduledAt = new \DateTimeImmutable('2021-08-27 12:00:00');
        $sendingAt = new \DateTimeImmutable('2021-08-27 12:00:00');
        $failed_at = new \DateTimeImmutable('2021-08-27 12:00:00');
        $sent_at = new \DateTimeImmutable('2021-08-27 12:00:00');

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'from_email' => 'thibault@hyvor.com',
                'subject' => 'Test subject',
                'from_name' => 'Thibault',
                'reply_to_email' => 'thibault@hyvor.com',
                'content' => 'Test content',
                'status' => 'draft',
                'scheduled_at' => $scheduledAt->getTimestamp(),
                'sending_at' => $sendingAt->getTimestamp(),
                'sent_at' => $sent_at->getTimestamp(),
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame('thibault@hyvor.com', $json['from_email']);
        $this->assertSame('Test subject', $json['subject']);
        $this->assertSame('Thibault', $json['from_name']);
        $this->assertSame('thibault@hyvor.com', $json['reply_to_email']);
        $this->assertSame('Test content', $json['content']);
        $this->assertSame('draft', $json['status']);
        $this->assertSame($scheduledAt->getTimestamp(), $json['scheduled_at']);
        $this->assertSame($sendingAt->getTimestamp(), $json['sending_at']);
        $this->assertSame($sent_at->getTimestamp(), $json['sent_at']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame('thibault@hyvor.com', $issue->getFromEmail());
        $this->assertSame('Test subject', $issue->getSubject());
        $this->assertSame('Thibault', $issue->getFromName());
        $this->assertSame('thibault@hyvor.com', $issue->getReplyToEmail());
        $this->assertSame('Test content', $issue->getContent());
        $this->assertSame(IssueStatus::DRAFT, $issue->getStatus());
        $this->assertSame('2021-08-27 12:00:00', $issue->getScheduledAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-27 12:00:00', $issue->getSendingAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-27 12:00:00', $issue->getSentAt()?->format('Y-m-d H:i:s'));
    }



    public function testCreateIssueWithInvalidList(): void
    {
        $project1 = ProjectFactory::createOne();
        $project2 = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project1]);

        $issue = IssueFactory::createOne(['project' => $project2]);

        $response = $this->consoleApi(
            $project2,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'lists' => [$list->getId()],
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('List with id ' . $list->getId() . ' not found', $json['message']);
    }

    /**
     * @param callable(Project): array<string, mixed> $input
     * @param array<mixed> $violations
     * @return void
     */
    private function validateInput(
        callable $input,
        array $violations
    ): void
    {
        $project = ProjectFactory::createOne();
        $issue = IssueFactory::createOne(['project' => $project]);

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/issues/' . $issue->getId(),
            $input($project),
        );
        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame($violations, $json['violations']);
        $this->assertSame('Validation failed with ' . count($violations) . ' violations(s)', $json['message']);
    }

    public function testInputValidationInvalidEmail(): void
    {
        $this->validateInput(
            fn (Project $project) => [
                'from_email' => 'not-email',
            ],
            [
                [
                    'property' => 'from_email',
                    'message' => 'This value is not a valid email address.',
                ],
            ]
        );
    }

    public function testInputValidationEmailTooLong(): void
    {
        $this->validateInput(
            fn (Project $project) => [
                'from_email' => str_repeat('a', 256) . '@hyvor.com',
            ],
            [
                [
                    'property' => 'from_email',
                    'message' => 'This value is too long. It should have 255 characters or less.',
                ],
            ]
        );
    }

    public function testInputValidationOptionalValues(): void
    {
        $this->validateInput(
            fn (Project $project) => [
                'status' => 'invalid-status',
                'scheduled_at' => 'invalid-date',
                'sending_at' => 'invalid-date',
                'failed_at' => 'invalid-date',
                'sent_at' => 'invalid-date',
            ],
            [
                [
                    'property' => 'status',
                    'message' => 'This value should be of type draft|scheduled|sending|failed|sent.',
                ],
                [
                    'property' => 'scheduled_at',
                    'message' => 'This value should be of type int.',
                ],
                [
                    'property' => 'sending_at',
                    'message' => 'This value should be of type int.',
                ],
                [
                    'property' => 'failed_at',
                    'message' => 'This value should be of type int.',
                ],
                [
                    'property' => 'sent_at',
                    'message' => 'This value should be of type int.',
                ],
            ]
        );
    }
}
