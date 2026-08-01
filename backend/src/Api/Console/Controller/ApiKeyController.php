<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\ApiKey\CreateApiKeyInput;
use App\Api\Console\Input\ApiKey\UpdateApiKeyInput;
use App\Api\Console\Object\ApiKeyObject;
use App\Entity\ApiKey;
use App\Entity\Newsletter;
use App\Service\ApiKey\ApiKeyService;
use App\Service\ApiKey\Dto\UpdateApiKeyDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class ApiKeyController extends AbstractController
{
    public function __construct(
        private ApiKeyService $apiKeyService,
    ) {}

    #[Route('/api-keys', methods: 'POST')]
    #[ScopeRequired(PostScope::API_KEYS_WRITE)]
    #[OA\Post(summary: 'Create a new API key', description: 'Creates a new API key for the specified newsletter. The API key will be returned in the response, and it is important to store it securely as it will not be retrievable again.')]
    public function createApiKey(#[MapRequestPayload] CreateApiKeyInput $input, Newsletter $newsletter): JsonResponse
    {
        $apiKeysCount = count($this->apiKeyService->getApiKeysForNewsletter($newsletter));
        if ($apiKeysCount >= ApiKeyService::MAX_API_KEY_PER_NEWSLETTER) {
            throw new BadRequestHttpException("You have reached the maximum number of API keys for this newsletter.");
        }

        $creation = $this->apiKeyService->createApiKey($newsletter, $input->name, $input->scopes);

        return $this->json(new ApiKeyObject($creation['apiKey'], $creation['rawKey']));
    }

    #[Route('/api-keys', methods: 'GET')]
    #[ScopeRequired(PostScope::API_KEYS_READ)]
    #[OA\Get(summary: 'Get all API keys for a newsletter')]
    public function getApiKeys(Newsletter $newsletter): JsonResponse
    {
        $apiKeys = $this->apiKeyService->getApiKeysForNewsletter($newsletter);
        $apiKeyObjects = array_map(fn(ApiKey $apiKey) => new ApiKeyObject($apiKey), $apiKeys);

        return $this->json($apiKeyObjects);
    }

    #[Route('/api-keys/{id}', methods: 'PATCH')]
    #[ScopeRequired(PostScope::API_KEYS_WRITE)]
    #[OA\Patch(summary: 'Update an API key')]
    public function updateApiKey(#[MapRequestPayload] UpdateApiKeyInput $input, ApiKey $apiKey): JsonResponse
    {
        $updates = new UpdateApiKeyDto();
        if ($input->has('is_enabled')) {
            $updates->enabled = $input->is_enabled;
        }
        if ($input->has('scopes')) {
            $updates->scopes = $input->scopes;
        }
        if ($input->has('name')) {
            $updates->name = $input->name;
        }

        $updatedApiKey = $this->apiKeyService->updateApiKey($apiKey, $updates);

        return $this->json(new ApiKeyObject($updatedApiKey));
    }

    #[Route('/api-keys/{id}', methods: 'POST')]
    #[ScopeRequired(PostScope::API_KEYS_WRITE)]
    #[OA\Post(summary: 'Regenerate an API key')]
    public function regenerateApiKey(ApiKey $apiKey): JsonResponse
    {
        $regeneration = $this->apiKeyService->regenerateApiKey($apiKey);

        return $this->json(new ApiKeyObject($regeneration['apiKey'], $regeneration['rawKey']));
    }

    #[Route('/api-keys/{id}', methods: 'DELETE')]
    #[ScopeRequired(PostScope::API_KEYS_WRITE)]
    #[OA\Delete(summary: 'Delete an API key')]
    public function deleteApiKey(ApiKey $apiKey): JsonResponse
    {
        $this->apiKeyService->deleteApiKey($apiKey);

        return $this->json([]);
    }
}
