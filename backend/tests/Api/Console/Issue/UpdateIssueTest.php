<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\IssueObject;
use App\Entity\Issue;
use App\Entity\Newsletter;
use App\Entity\Type\IssueStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\IssueService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

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
        $newsletter = NewsletterFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $issue = IssueFactory::createOne(['newsletter' => $newsletter, 'list_ids' => [$list1->getId()]]);

        $response = $this->consoleApi(
            $newsletter,
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

        $json = $this->getJson();
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

    public function testUpdateDraftIssueContent(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $list2 = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'status' => IssueStatus::DRAFT,
            'list_ids' => [$list1->getId()]
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'subject' => 'Test subject',
                'content' => 'Test content',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame('Test subject', $json['subject']);
        $this->assertSame('Test content', $json['content']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame('Test subject', $issue->getSubject());
        $this->assertSame('Test content', $issue->getContent());

    }

    public function testCreateIssueWithInvalidList(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter1]);

        $issue = IssueFactory::createOne(['newsletter' => $newsletter2]);

        $response = $this->consoleApi(
            $newsletter2,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'lists' => [$list->getId()],
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('List with id ' . $list->getId() . ' not found', $json['message']);
    }


    public function testUpdateIssueWrongNewsletter(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        $issue = IssueFactory::createOne(['newsletter' => $newsletter1]);

        $response = $this->consoleApi(
            $newsletter2,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'subject' => 'Test subject',
            ]
        );

        $this->assertSame(403, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Entity does not belong to the newsletter', $json['message']);
    }

    /**
     * @param callable(Newsletter): array<string, mixed> $input
     * @param array<mixed> $violations
     * @return void
     */
    private function validateInput(
        callable $input,
        array    $violations
    ): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/issues/' . $issue->getId(),
            $input($newsletter),
        );
        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame($violations, $json['violations']);
        $this->assertSame('Validation failed with ' . count($violations) . ' violations(s)', $json['message']);
    }

    public function testInputValidationInvalidEmail(): void
    {
        $this->validateInput(
            fn(Newsletter $newsletter) => [
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
            fn(Newsletter $newsletter) => [
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
