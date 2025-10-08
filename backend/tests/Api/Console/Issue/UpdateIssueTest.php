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
use App\Tests\Factory\SendingProfileFactory;
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

        $sendingProfile = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'from_email' => 'thibault@hyvor.com',
            'from_name' => 'Thibault',
            'reply_to_email' => 'supun@hyvor.com',
        ]);
        $issue = IssueFactory::createOne([
            'newsletter' => $newsletter,
            'list_ids' => [$list1->getId()],
            'sending_profile' => $sendingProfile,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/issues/' . $issue->getId(),
            [
                'subject' => 'Test subject',
                'list_ids' => [$list1->getId(), $list2->getId()],
                'content' => 'Test content',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame($sendingProfile->getId(), $json['sending_profile_id']);
        $this->assertSame('Test subject', $json['subject']);
        $this->assertSame('Test content', $json['content']);

        $repository = $this->em->getRepository(Issue::class);
        $issue = $repository->find($json['id']);
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertSame($sendingProfile->getId(), $issue->getSendingProfile()->getId());
        $this->assertSame('Test subject', $issue->getSubject());
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
}
