<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Service\EmailTemplate\HtmlEmailTemplateRenderer;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(HtmlEmailTemplateRenderer::class)]
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
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/test",
            [
                'email' => 'thibault@hyvor.com'
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
            ]
        );

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/test",
            [
                'email' => 'thibault'
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Validation failed with 1 violations(s)', $json['message']);
        $this->assertViolationCount(1);
        $this->assertHasViolation('email', 'This value is not a valid email address.');
    }
}
