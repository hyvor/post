<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Service\Template\TemplateRenderer;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(TemplateRenderer::class)]
class SendTestIssueTest extends WebTestCase
{

    public function test_send_test(): void
    {
        $project = ProjectFactory::createOne();
        $issue = IssueFactory::createOne(
            [
                'project' => $project,
                'subject' => 'Test subject',
                'content' => 'Test content',
            ]
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            "/issues/" . $issue->getId() . "/test?email=thibault%40hyvor.com"
        );

        $this->assertSame(200, $response->getStatusCode());

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'Test subject');
    }

    public function test_send_invalid_email(): void
    {
        $project = ProjectFactory::createOne();
        $issue = IssueFactory::createOne(
            [
                'project' => $project,
                'subject' => 'Test subject',
                'content' => 'Test content',
            ]
        );

        $response = $this->consoleApi(
            $project,
            'POST',
            "/issues/" . $issue->getId() . "/test?email=thibault"
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Invalid email address.', $json['message']);
    }
}
