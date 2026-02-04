<?php

namespace App\Tests\Api\Console\Issue;

use App\Entity\Type\IssueStatus;
use App\Entity\Type\UserRole;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Auth\AuthFake;

class GetTestIssueDataTest extends WebTestCase
{
    public function test_get_test_issue_data(): void
    {
        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'nadil',
            'name' => 'Nadil Karunarathna',
            'email' => 'nadil@hyvor.com'
        ]);

        $newsletter = NewsletterFactory::createOne([
            'test_sent_emails' => [
                'nadil@hyvor.com',
                'supun@hyvor.com'
            ]
        ]);
        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
                'subject' => 'Test subject',
                'content' => 'Test content',
                'status' => IssueStatus::DRAFT
            ]
        );

        UserFactory::createOne([
            'newsletter_id' => $newsletter->getId(),
            'hyvor_user_id' => 15,
            'role' => UserRole::OWNER
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            "/issues/" . $issue->getId() . "/test",
        );

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getContent();
        $this->assertNotFalse($data);
        $json = json_decode($data, true);
        $this->assertIsArray($json);
        $this->assertIsArray($json['suggested_emails']);
        $this->assertCount(2, $json['suggested_emails']);
        $this->assertIsArray($json['test_sent_emails']);
        $this->assertCount(2, $json['test_sent_emails']);
    }
}