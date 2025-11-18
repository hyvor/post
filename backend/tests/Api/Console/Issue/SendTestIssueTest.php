<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Type\IssueStatus;
use App\Service\Template\HtmlTemplateRenderer;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(IssueController::class)]
#[CoversClass(HtmlTemplateRenderer::class)]
class SendTestIssueTest extends WebTestCase
{

    public function test_send_test(): void
    {
        $callback = function ($method, $url, $options): JsonMockResponse {

            $this->assertSame('POST', $method);
            $this->assertSame('https://relay.hyvor.com/api/console/sends', $url);
            $body = json_decode($options['body'], true);
            $this->assertIsArray($body);
            $this->assertSame('Test subject', $body['subject']);
            return new JsonMockResponse();
        };

        $this->mockRelayClient($callback);

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
        $this->assertHasViolation('emails[1]', 'This value is not a valid email address.');
    }
}
