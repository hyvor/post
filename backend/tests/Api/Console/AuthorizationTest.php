<?php

namespace App\Tests\Api\Console;

use Hyvor\Internal\Auth\Oidc\Testing\OidcTestingUtils;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleApiAuthorizationListenerAbstract;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
use Hyvor\Internal\CloudApi\JwtSource\JwtSource;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Entity\ApiKey;
use App\Entity\Newsletter;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Component\Component;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Hyvor\Internal\CloudApi\CloudApiService;
use Hyvor\Internal\CloudApi\Scope\ScopeBuilder;

#[CoversClass(ScopeRequired::class)]
class AuthorizationTest extends WebTestCase
{
    use ClockSensitiveTrait;

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
            $this->getJson()["message"],
        );
    }

    public function test_wrong_authorization_header(): void
    {
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_AUTHORIZATION" => "WrongHeader",
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            'Authorization header must be a Bearer token',
            $this->getJson()["message"],
        );
    }

    public function test_missing_bearer_token(): void
    {
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_AUTHORIZATION" => "Bearer ",
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertIsString($this->getJson()["message"]);
        $this->assertStringContainsString(
            "Invalid Cloud API token", // weird error because it branches out to cloud API token
            $this->getJson()["message"],
        );
    }

    public function test_invalid_api_key(): void
    {
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_AUTHORIZATION" => "Bearer " . bin2hex(random_bytes(16)),
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("API key is invalid or does not exist.", $this->getJson()["message"]);
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
            ],
        );
        $this->assertResponseStatusCodeSame(401);
        $this->assertSame("Unauthorized", $this->getJson()["message"]);
    }

    public function test_fails_when_organization_is_required(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "User does not have a valid current organization, or the organization is not found.",
            $this->getJson()["message"],
        );
    }

    public function test_fails_organization_mismatch(): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin',
            ),
        );

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_ORGANIZATION_ID" => 2,
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("org_mismatch", $this->getJson()["message"]);
    }

    public function test_fails_when_xnewsletterid_header_is_not_set(): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin',
            ),
        );

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_ORGANIZATION_ID" => 1,
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "Unable to find the newsletter from the request. Please provide a valid x-newsletter-id header.",
            $this->getJson()["message"],
        );
    }

    public function test_invalid_newsletter_id(): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin',
            ),
        );
        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_ORGANIZATION_ID" => 1,
                "HTTP_X_NEWSLETTER_ID" => "999",
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "Unable to find the newsletter from the request. Please provide a valid x-newsletter-id header.",
            $this->getJson()["message"],
        );
    }

    public function test_newsletter_does_not_belong_to_current_organization(): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin',
            ),
        );
        $newsletter = NewsletterFactory::createOne(['organization_id' => 2]);
        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_ORGANIZATION_ID" => 1,
                "HTTP_X_NEWSLETTER_ID" => $newsletter->getId(),
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "does_not_belong_the_resource",
            $this->getJson()["message"],
        );
    }

    public function test_user_not_authorized_for_newsletter(): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin',
            ),
        );
        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);
        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_ORGANIZATION_ID" => 1,
                "HTTP_X_NEWSLETTER_ID" => $newsletter->getId(),
            ],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "You do not have access to this resource.",
            $this->getJson()["message"],
        );
    }

    public function test_missing_scope_required_attribute(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $this->consoleApi(
            $newsletter,
            'GET',
            '/issues',
            scopes: [PostScope::ISSUES_WRITE],
        );
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame(
            "You do not have the required scope 'issues.read' to access this resource.",
            $this->getJson()["message"],
        );
    }

    public function test_authorizes_via_api_key_and_updates_last_usage(): void
    {
        static::mockTime(new \DateTimeImmutable('2025-06-01 00:00:00'));

        $newsletter = NewsletterFactory::createOne();
        $this->consoleApi(
            $newsletter,
            'GET',
            '/issues',
            scopes: [PostScope::ISSUES_READ],
        );
        $this->assertResponseStatusCodeSame(200);

        $authResults = $this->client->getRequest()->attributes->get(
            ConsoleApiAuthorizationListenerAbstract::ATTRIBUTE_KEY,
        );
        $this->assertInstanceOf(
            ConsoleAuthResults::class,
            $authResults,
        );

        $newsletterFromAttr = $authResults->getResource();
        $this->assertInstanceOf(Newsletter::class, $newsletterFromAttr);
        $this->assertSame($newsletter->getId(), $newsletterFromAttr->getId());

        $apiKey = $this->em->getRepository(ApiKey::class)->findOneBy(['newsletter' => $newsletter]);

        $this->assertInstanceOf(ApiKey::class, $apiKey);
        $this->assertSame(
            '2025-06-01 00:00:00',
            $apiKey->getLastAccessedAt()?->format('Y-m-d H:i:s'),
        );
    }

    public function test_authorizes_via_session(): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin',
            ),
        );
        $newsletter = NewsletterFactory::createOne([
            'organization_id' => 1,
        ]);
        UserFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
        ]);

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_ORGANIZATION_ID" => 1,
                "HTTP_X_NEWSLETTER_ID" => $newsletter->getId(),
            ],
        );
        $this->assertResponseStatusCodeSame(200);

        $authResults = $this->client->getRequest()->attributes->get(
            ConsoleApiAuthorizationListenerAbstract::ATTRIBUTE_KEY,
        );
        $this->assertInstanceOf(
            ConsoleAuthResults::class,
            $authResults,
        );

        $newsletterFromAttr = $authResults->getResource();
        $this->assertInstanceOf(
            Newsletter::class,
            $newsletterFromAttr,
        );
        $this->assertSame($newsletter->getId(), $newsletterFromAttr->getId());

        $this->assertNotNull($authResults->getNullableUser());
        $this->assertSame(1, $authResults->getNullableUser()->id);
    }

    public function test_user_level_endpoint_works(): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin',
            ),
        );
        BillingFake::enableForSymfony(
            $this->container,
            [1 => new ResolvedLicense(ResolvedLicenseType::TRIAL, PostLicense::trial())],
        );

        $newsletter = NewsletterFactory::createOne([
            'organization_id' => 1,
        ]);
        UserFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
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

    public function test_when_no_organization_is_required(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);

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

    // cloud JWT

    public function test_works_with_cloud_jwt(): void
    {
        $orgId = 15;

        $cloudApiService = $this->getService(CloudApiService::class);
        $key = OidcTestingUtils::generateKey();

        $scopeBuilder = new ScopeBuilder();
        $scopeBuilder->addScopes(Component::POST, [PostScope::ISSUES_READ]);

        $jwt = $cloudApiService->createJwtToken(
            $orgId,
            $scopeBuilder,
            JwtSource::forCloud('testkey'),
        );

        $this->client->request(
            "GET",
            "/api/console/issues",
            server: [
                "HTTP_X_ORGANIZATION_ID" => $orgId,
                'HTTP_AUTHORIZATION' => 'Bearer ' . $jwt->encode($key['privateKeyPem'], 'testkey'),
            ],
        );
        $this->assertResponseStatusCodeSame(403);
    }

}
