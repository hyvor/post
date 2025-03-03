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
    #[TestWith(['failed', 'failed_at'])]
    #[TestWith(['sent', 'sent_at'])]
    public function testUpdateIssueStatuses(string $status, string $dateField): void
    {
        $this->updateIssueStatus($status, $dateField);
    }


    public function testCreateIssueWithAllInputs(): void
    {
        $project = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project]);

        $scheduledAt = new \DateTimeImmutable('2021-08-27 12:00:00');
        $sendingAt = new \DateTimeImmutable('2021-08-27 12:00:00');
        $failed_at = new \DateTimeImmutable('2021-08-27 12:00:00');
        $sent_at = new \DateTimeImmutable('2021-08-27 12:00:00');

        $response = $this->consoleApi(
            $project,
            'POST',
            '/issues',
            [
                'list_id' => $list->getId(),
                'from_email' => 'thibault@hyvor.com',
                'subject' => 'Test subject',
                'from_name' => 'Thibault',
                'reply_to_email' => 'thibault@hyvor.com',
                'content' => 'Test content',
                'status' => 'draft',
                'html' => 'Test html',
                'text' => 'Test text',
                'error_private' => 'Test error private',
                'batch_id' => 1,
                'scheduled_at' => $scheduledAt->getTimestamp(),
                'sending_at' => $sendingAt->getTimestamp(),
                'failed_at' => $failed_at->getTimestamp(),
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
        $this->assertSame('Test html', $json['html']);
        $this->assertSame('Test text', $json['text']);
        $this->assertSame('Test error private', $json['error_private']);
        $this->assertSame(1, $json['batch_id']);
        $this->assertSame($scheduledAt->getTimestamp(), $json['scheduled_at']);
        $this->assertSame($sendingAt->getTimestamp(), $json['sending_at']);
        $this->assertSame($failed_at->getTimestamp(), $json['failed_at']);
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
        $this->assertSame('Test html', $issue->getHtml());
        $this->assertSame('Test text', $issue->getText());
        $this->assertSame('Test error private', $issue->getErrorPrivate());
        $this->assertSame(1, $issue->getBatchId());
        $this->assertSame('2021-08-27 12:00:00', $issue->getScheduledAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-27 12:00:00', $issue->getSendingAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-27 12:00:00', $issue->getFailedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('2021-08-27 12:00:00', $issue->getSentAt()?->format('Y-m-d H:i:s'));
    }



    public function testCreateIssueWithInvalidList(): void
    {
        $project1 = ProjectFactory::createOne();
        $project2 = ProjectFactory::createOne();

        $list = NewsletterListFactory::createOne(['project' => $project1]);

        $response = $this->consoleApi(
            $project2,
            'POST',
            '/issues',
            [
                'list_id' => $list->getId(),
                'from_email' => 'thibault@hyvor.com',
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

        $response = $this->consoleApi(
            $project,
            'POST',
            '/issues',
            $input($project),
        );
        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame($violations, $json['violations']);
        $this->assertSame('Validation failed with ' . count($violations) . ' violations(s)', $json['message']);
    }

    public function testInputValidationEmptyFromEmailAndListIds(): void
    {
        $this->validateInput(
            fn (Project $project) => [],
            [
                [
                    'property' => 'list_id',
                    'message' => 'This value should not be blank.',
                ],
                [
                    'property' => 'from_email',
                    'message' => 'This value should not be blank.',
                ]
            ]
        );
    }

    public function testInputValidationInvalidEmail(): void
    {
        $this->validateInput(
            fn (Project $project) => [
                'from_email' => 'not-email',
            ],
            [
                [
                    'property' => 'list_id',
                    'message' => 'This value should not be blank.',
                ],
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
                    'property' => 'list_id',
                    'message' => 'This value should not be blank.',
                ],
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
                    'message' => 'This value should be of type int|null.',
                ],
                [
                    'property' => 'sending_at',
                    'message' => 'This value should be of type int|null.',
                ],
                [
                    'property' => 'failed_at',
                    'message' => 'This value should be of type int|null.',
                ],
                [
                    'property' => 'sent_at',
                    'message' => 'This value should be of type int|null.',
                ],
            ]
        );
    }
}
