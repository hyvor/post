<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Type\IssueStatus;
use App\Service\Template\HtmlTemplateRenderer;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(HtmlTemplateRenderer::class)]
class SendTestIssueTest extends WebTestCase
{

    public function test_send_test(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
                'subject' => 'Test subject',
                'content' => 'Test content',
                'status' => IssueStatus::DRAFT
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/test",
            [
                'emails' => [
                    'thibault@hyvor.com'
                ]
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'Test subject');
    }

    public function test_send_invalid_email(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
                'subject' => 'Test subject',
                'content' => 'Test content',
                'status' => IssueStatus::DRAFT
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/test",
            [
                'emails' => [
                    'nadil@hyvor.com',
                    'thibault'
                ]
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('This value is not a valid email address.', $json['message']);
        $this->assertHasViolation('emails[1]', 'This value is not a valid email address.');
    }
}
