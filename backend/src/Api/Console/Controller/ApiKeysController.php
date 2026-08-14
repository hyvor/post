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
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class ApiKeysController extends AbstractController
{
    public function __construct(
        private ApiKeyService $apiKeyService,
    ) {}

    #[Route('/api-keys', methods: 'POST')]
    #[ScopeRequired(PostScope::API_KEYS_WRITE)]
    #[OA\Post(summary: 'Create a new API key', description: 'Creates a new API key for the specified newsletter. The API key will be returned in the response, and it is important to store it securely as it will not be retrievable again.')]
    #[OA\Response(
        response: 201,
        description: 'Returns the created API key object. The `key` property is the raw key, which is only returned once.',
        content: new Model(type: ApiKeyObject::class),
    )]
    public function create(#[MapRequestPayload] CreateApiKeyInput $input, Newsletter $newsletter): JsonResponse
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
    #[OA\Get(summary: 'Get all API keys for a newsletter', description: 'Returns all API keys of the newsletter. The raw key is not returned; only its metadata is.')]
    #[OA\Response(
        response: 200,
        description: 'List of API keys',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ApiKeyObject::class)),
        ),
    )]
    public function list(Newsletter $newsletter): JsonResponse
    {
        $apiKeys = $this->apiKeyService->getApiKeysForNewsletter($newsletter);
        $apiKeyObjects = array_map(fn(ApiKey $apiKey) => new ApiKeyObject($apiKey), $apiKeys);

        return $this->json($apiKeyObjects);
    }

    #[Route('/api-keys/{id}', methods: 'PATCH')]
    #[ScopeRequired(PostScope::API_KEYS_WRITE)]
    #[OA\Patch(summary: 'Update an API key', description: 'Updates the name, scopes, or enabled status of an API key.')]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated API key object.',
        content: new Model(type: ApiKeyObject::class),
    )]
    public function update(#[MapRequestPayload] UpdateApiKeyInput $input, ApiKey $apiKey): JsonResponse
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
    #[OA\Post(summary: 'Regenerate an API key', description: 'Regenerates the raw key of an API key. The previous key is invalidated immediately, and the new raw key is returned once.')]
    #[OA\Response(
        response: 200,
        description: 'Returns the API key object with the newly generated raw key.',
        content: new Model(type: ApiKeyObject::class),
    )]
    public function regenerate(ApiKey $apiKey): JsonResponse
    {
        $regeneration = $this->apiKeyService->regenerateApiKey($apiKey);

        return $this->json(new ApiKeyObject($regeneration['apiKey'], $regeneration['rawKey']));
    }

    #[Route('/api-keys/{id}', methods: 'DELETE')]
    #[ScopeRequired(PostScope::API_KEYS_WRITE)]
    #[OA\Delete(summary: 'Delete an API key', description: 'Permanently deletes an API key. Requests made with the deleted key will be rejected immediately.')]
    #[OA\Response(
        response: 200,
        description: 'Returns an empty object on success.',
        content: new OA\JsonContent(),
    )]
    public function delete(ApiKey $apiKey): JsonResponse
    {
        $this->apiKeyService->deleteApiKey($apiKey);

        return $this->json([]);
    }
}
