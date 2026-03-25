<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\RelayDomainStatus;
use App\Service\Template\HtmlTemplateRenderer;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use function Zenstruck\Foundry\Persistence\refresh;

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
        DomainFactory::createOne([
            'organization_id' => $newsletter->getOrganizationId(),
            'domain' => 'hyvor.com',
            'relay_status' => RelayDomainStatus::ACTIVE
        ]);
        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
                'subject' => 'Test subject',
                'status' => IssueStatus::DRAFT
            ]
        );

        $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/test",
            [
                'emails' => [
                    'thibault@hyvor.com',
                    'nadil@hyvor.com'
                ]
            ]
        );

        $this->assertResponseIsSuccessful();

        $json = $this->getJson();
        $this->assertSame(2, $json['success_count']);
        $this->assertSame(2, refresh($issue)->getTestEmailsSent());
    }

    public function test_send_invalid_email(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
                'subject' => 'Test subject',
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

    public function test_does_not_allow_test_emails_to_unauthorized_addresses(): void
    {
        $newsletter = NewsletterFactory::createOne();
        DomainFactory::createOne([
            'organization_id' => $newsletter->getOrganizationId(),
            'domain' => 'hyvor.com',
            'relay_status' => RelayDomainStatus::ACTIVE
        ]);
        $issue = IssueFactory::createOne(
            [
                'newsletter' => $newsletter,
                'subject' => 'Test subject',
                'status' => IssueStatus::DRAFT,
                'test_emails_sent' => 10
            ]
        );

        $this->consoleApi(
            $newsletter,
            'POST',
            "/issues/" . $issue->getId() . "/test",
            [
                'emails' => [
                    'nadil@hyvor.com',
                    'nadil@example.com'
                ]
            ]
        );

        $this->assertResponseFailed(422, 'Test emails can only be sent to verified domains or emails of newsletter users.');
        $this->assertSame(10, refresh($issue)->getTestEmailsSent());
    }
}
