<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\Project;
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
#[CoversClass(IssueObject::class)]
class UpdateIssueTest extends WebTestCase
{

    // TODO: tests for authentication

    public function testUpdateIssueAllFields(): void
    {
        $project = ProjectFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => $project]);

        $issue = IssueFactory::createOne(['project' => $project, 'list_ids' => [$list1->getId()]]);

        $response = $this->consoleApi(
            $project,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'subject' => 'Test subject',
                'from_name' => 'Thibault',
                'list_ids' => [$list1->getId(), $list2->getId()],
                'from_email' => 'thibault@hyvor.com',
                'reply_to_email' => 'supun@hyvor.com',
                'content' => 'Test content',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame('thibault@hyvor.com', $json['from_email']);
        $this->assertSame('Test subject', $json['subject']);
        $this->assertSame('Thibault', $json['from_name']);
        $this->assertSame('supun@hyvor.com', $json['reply_to_email']);
        $this->assertSame('Test content', $json['content']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame('thibault@hyvor.com', $issue->getFromEmail());
        $this->assertSame('Test subject', $issue->getSubject());
        $this->assertSame('Thibault', $issue->getFromName());
        $this->assertSame('supun@hyvor.com', $issue->getReplyToEmail());
        $this->assertSame('Test content', $issue->getContent());
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


    public function testUpdateIssueWrongProject(): void
    {
        $project1 = ProjectFactory::createOne();
        $project2 = ProjectFactory::createOne();

        $issue = IssueFactory::createOne(['project' => $project1]);

        $response = $this->consoleApi(
            $project2,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'subject' => 'Test subject',
            ]
        );

        $this->assertSame(403, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Entity does not belong to the project', $json['message']);
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
}
