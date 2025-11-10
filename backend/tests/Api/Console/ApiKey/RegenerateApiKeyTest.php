<?php

namespace App\Tests\Api\Console\ApiKey;

use App\Api\Console\Controller\ApiKeyController;
use App\Api\Console\Object\ApiKeyObject;
use App\Entity\ApiKey;
use App\Service\ApiKey\ApiKeyService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApiKeyFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApiKeyController::class)]
#[CoversClass(ApiKeyService::class)]
#[CoversClass(ApiKeyObject::class)]
class RegenerateApiKeyTest extends WebTestCase
{
    public function test_regenerate_api_key(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $apiKey = ApiKeyFactory::createOne([
            'newsletter' => $newsletter,
            'is_enabled' => true,
        ]);
        $oldKeyHashed = $apiKey->getKeyHashed();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/api-keys/' . $apiKey->getId()
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $this->getJson();

        $this->assertArrayHasKey('key', $content);
        $this->assertNotNull($content['key']);
        $this->assertNotSame($apiKey->getKeyHashed(), $content['key']);

        $apiKeyDb = $this->em->getRepository(ApiKey::class)
            ->find($content['id']);
        $this->assertNotNull($apiKeyDb);
        $this->assertNotSame($oldKeyHashed, $apiKeyDb->getKeyHashed());
    }
}
