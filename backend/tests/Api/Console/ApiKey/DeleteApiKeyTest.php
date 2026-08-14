<?php

namespace App\Tests\Api\Console\ApiKey;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use App\Api\Console\Controller\ApiKeysController;
use App\Api\Console\Object\ApiKeyObject;
use App\Entity\ApiKey;
use App\Service\ApiKey\ApiKeyService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeysController::class)]
#[CoversClass(ApiKeyService::class)]
#[CoversClass(PostScope::class)]
#[CoversClass(ApiKeyObject::class)]
class DeleteApiKeyTest extends WebTestCase
{
    public function test_delete_api_key(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $apiKey = ApiKeyFactory::createOne(
            [
                'newsletter' => $newsletter,
                'is_enabled' => true,
            ],
        );

        $apiKeyId = $apiKey->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/api-keys/' . $apiKey->getId(),
        );

        $this->assertSame(200, $response->getStatusCode());

        $deletedApiKey = $this->em->getRepository(ApiKey::class)->find($apiKeyId);
        $this->assertNull($deletedApiKey);
    }

    public function test_delete_non_existent_api_key(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/api-keys/999999',
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
