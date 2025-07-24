<?php

namespace App\Tests\Api\Sudo;

use App\Api\Sudo\Authorization\SudoAuthorizationListener;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\SudoUserFactory;
use Hyvor\Internal\Auth\AuthFake;
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
        AuthFake::enableForSymfony($this->container, ['id' => 1]);

        $response = $this->consoleApi(
            null,
            'GET',
            '/init'
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_sudo_api_access_with_valid_sudo_user(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);
        SudoUserFactory::createOne([
                'user_id' => 1,
            ]);
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
        $this->assertSame("Invalid session.", $this->getJson()["message"]);
    }

    public function test_sudo_api_access_with_invalid_sudo_user(): void
    {
        AuthFake::enableForSymfony($this->container, ['id' => 1]);
        $this->client->getCookieJar()->set(new Cookie('authsess', 'validSession'));
        $this->client->request("GET", "/api/sudo/approvals");
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("You do not have sudo access.", $this->getJson()["message"]);
    }

    public function test_sudo_api_access_without_session(): void
    {
        $this->client->request("GET", "/api/sudo/approvals");
        $this->assertResponseStatusCodeSame(403);
        $this->assertSame("Session authentication required for sudo API access.", $this->getJson()["message"]);
    }
}
