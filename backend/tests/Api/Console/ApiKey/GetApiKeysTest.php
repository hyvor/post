<?php

namespace App\Tests\Api\Console\ApiKey;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use App\Api\Console\Controller\ApiKeysController;
use App\Api\Console\Object\ApiKeyObject;
use App\Service\ApiKey\ApiKeyService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeysController::class)]
#[CoversClass(ApiKeyService::class)]
#[CoversClass(PostScope::class)]
#[CoversClass(ApiKeyObject::class)]
class GetApiKeysTest extends WebTestCase
{
    public function test_get_api_keys(): void
    {
        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);
        ApiKeyFactory::createMany(4, [
            'newsletter' => $newsletter,
            'scopes' => [PostScope::ISSUES_READ],
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/api-keys',
            useSession: true,
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $this->getJson();
        $this->assertCount(4, $content);
        foreach ($content as $key => $apiKeyData) {
            $this->assertIsArray($apiKeyData);
            $this->assertArrayHasKey('id', $apiKeyData);
            $this->assertArrayHasKey('name', $apiKeyData);
            $this->assertArrayHasKey('scopes', $apiKeyData);
            $this->assertArrayHasKey('created_at', $apiKeyData);
            $this->assertArrayHasKey('is_enabled', $apiKeyData);
            $this->assertArrayHasKey('last_accessed_at', $apiKeyData);
        }
    }

    public function test_get_api_keys_empty(): void
    {
        $newsletter = NewsletterFactory::createOne(['organization_id' => 1]);
        $response = $this->consoleApi(
            $newsletter,
            'GET',
            '/api-keys',
            useSession: true,
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $this->getJson();
        $this->assertEmpty($content);
    }
}
