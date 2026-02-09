<?php

namespace App\Tests\Api\Sudo;

use App\Api\Sudo\Authorization\SudoAuthorizationListener;
use App\Tests\Case\WebTestCase;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use Hyvor\Internal\Sudo\SudoUserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\BrowserKit\Cookie;

#[CoversClass(SudoAuthorizationListener::class)]
class SudoAuthorizationListenerTest extends WebTestCase
{
    protected function shouldEnableAuthFake(): bool
    {
        return false;
    }

    public function test_ignores_non_sudo_api_requests(): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin'
            )
        );
        BillingFake::enableForSymfony(
            $this->container,
            [1 => new ResolvedLicense(ResolvedLicenseType::TRIAL, PostLicense::trial())]
        );

        $response = $this->consoleApi(
            null,
            'GET',
            '/init',
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_sudo_api_access_with_valid_sudo_user(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);
        SudoUserFactory::createOne(['user_id' => 1]);

        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request("GET", "/api/sudo/approvals");
        $this->assertResponseStatusCodeSame(200);
    }

    public function test_sudo_api_access_with_guest_user(): void
    {
        AuthFake::enableForSymfony($this->container, null);
        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request("GET", "/api/sudo/approvals");
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("Not logged in", $this->getJson()["message"]);
    }

    public function test_sudo_api_access_with_invalid_sudo_user(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 9999]);
        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request("GET", "/api/sudo/approvals");
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("You do not have sudo access.", $this->getJson()["message"]);
    }
}
