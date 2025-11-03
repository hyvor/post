<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Authorization\AuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Entity\ApiKey;
use App\Entity\Newsletter;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUser;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(AuthorizationListener::class)]
#[CoversClass(ScopeRequired::class)]
class AuthorizationTest extends WebTestCase
{

    protected function shouldEnableAuthFake(): bool
    {
        return false;
    }

    public function test_api_key_authentication_nothing(): void
    {
        $this->client->request("GET", "/api/console/issues");
        $this->assertResponseStatusCodeSame(401);
        $this->assertSame(
            "Unauthorized",
            $this->getJson()["message"]
        );
    }

    public function test_wrong_authorization_header(): void
    {
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_AUTHORIZATION" => "WrongHeader",
            ]
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            'Authorization header must start with "Bearer ".',
            $this->getJson()["message"]
        );
    }

    public function test_missing_bearer_token(): void
    {
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_AUTHORIZATION" => "Bearer ",
            ]
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "API key is missing or empty.",
            $this->getJson()["message"]
        );
    }

    public function test_invalid_api_key(): void
    {
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_AUTHORIZATION" => "Bearer InvalidApiKey",
            ]
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("Invalid API key.", $this->getJson()["message"]);
    }

    public function test_invalid_newsletter_id(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);
        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_NEWSLETTER_ID" => "999",
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("Invalid newsletter ID.", $this->getJson()["message"]);
    }

    public function test_invalid_session(): void
    {
        AuthFake::enableForSymfony($this->container, null);

        $newsletter = NewsletterFactory::createOne();

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_NEWSLETTER_ID" => $newsletter->getId(),
            ]
        );
        $this->assertResponseStatusCodeSame(401);
        $this->assertSame("Unauthorized", $this->getJson()["message"]);
    }

    public function test_fails_when_xnewsletterid_header_is_not_set(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("X-Newsletter-ID is required for this endpoint.", $this->getJson()["message"]);
    }

    public function test_user_not_authorized_for_newsletter(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);

        $newsletter = NewsletterFactory::createOne();
        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_NEWSLETTER_ID" => $newsletter->getId(),
            ]
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "You do not have access to this newsletter.",
            $this->getJson()["message"]
        );
    }

    public function test_missing_scope_required_attribute(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $this->consoleApi(
            $newsletter,
            'GET',
            '/issues',
            scopes: [Scope::ISSUES_WRITE]
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "You do not have the required scope 'issues.read' to access this resource.",
            $this->getJson()["message"]
        );
    }

    public function test_authorizes_via_api_key_and_updates_last_usage(): void
    {
        Clock::set(new MockClock('2025-06-01 00:00:00'));

        $newsletter = NewsletterFactory::createOne();
        $this->consoleApi(
            $newsletter,
            'GET',
            '/issues',
            scopes: [Scope::ISSUES_READ]
        );
        $this->assertResponseStatusCodeSame(200);

        $newsletterFromAttr = $this->client->getRequest()->attributes->get('console_api_resolved_newsletter');
        $this->assertInstanceOf(
            Newsletter::class,
            $newsletterFromAttr
        );
        $this->assertSame($newsletter->getId(), $newsletterFromAttr->getId());

        $apiKey = $this->em->getRepository(ApiKey::class)->findOneBy(['newsletter' => $newsletter->_real()]);

        $this->assertInstanceOf(ApiKey::class, $apiKey);
        $this->assertSame(
            '2025-06-01 00:00:00',
            $apiKey->getLastAccessedAt()?->format('Y-m-d H:i:s')
        );
    }

    public function test_authorizes_via_session(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);

        $newsletter = NewsletterFactory::createOne([
            'user_id' => 1
        ]);
        UserFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter
        ]);

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_NEWSLETTER_ID" => $newsletter->getId(),
            ]
        );
        $this->assertResponseStatusCodeSame(200);

        $newsletterFromAttr = $this->client->getRequest()->attributes->get('console_api_resolved_newsletter');
        $this->assertInstanceOf(
            Newsletter::class,
            $newsletterFromAttr
        );
        $this->assertSame($newsletter->getId(), $newsletterFromAttr->getId());

        $userFromAttr = $this->client->getRequest()->attributes->get('console_api_resolved_user');
        $this->assertInstanceOf(AuthUser::class, $userFromAttr);
        $this->assertSame(1, $userFromAttr->id);
    }

    public function test_user_level_endpoint_works(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);

        $newsletter = NewsletterFactory::createOne([
            'user_id' => 1
        ]);
        UserFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter
        ]);

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/init",
        );
        $this->assertResponseStatusCodeSame(200);

        $json = $this->getJson();
        $this->assertArrayHasKey('newsletters', $json);
        $this->assertArrayHasKey('config', $json);
    }
}
