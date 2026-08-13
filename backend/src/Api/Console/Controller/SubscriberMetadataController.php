<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\SubscriberMetadata\CreateSubscriberMetadataDefinitionInput;
use App\Api\Console\Input\SubscriberMetadata\UpdateSubscriberMetadataDefinitionInput;
use App\Api\Console\Object\SubscriberMetadataDefinitionObject;
use App\Entity\Newsletter;
use App\Entity\SubscriberMetadataDefinition;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class SubscriberMetadataController extends AbstractController
{

    public function __construct(
        private SubscriberMetadataService $subscriberMetadataService,
    ) {}

    #[Route('/subscriber-metadata-definitions', methods: 'POST')]
    #[ScopeRequired(PostScope::SUBSCRIBERS_WRITE)]
    #[OA\Post(
        description: 'Creates a new subscriber metadata definition for the newsletter.',
        summary: 'Create a subscriber metadata definition',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created subscriber metadata definition object.',
        content: new Model(type: SubscriberMetadataDefinitionObject::class),
    )]
    public function createMetadata(
        Newsletter $newsletter,
        #[MapRequestPayload] CreateSubscriberMetadataDefinitionInput $input,
    ): JsonResponse {
        $current = $this->subscriberMetadataService->getMetadataDefinitionByKey($newsletter, $input->key);

        if ($current) {
            throw new BadRequestException('Key already exists');
        }

        $count = $this->subscriberMetadataService->getMetadataDefinitionsCount($newsletter);

        if ($count >= SubscriberMetadataService::MAX_METADATA_DEFINITIONS_PER_NEWSLETTER) {
            throw new BadRequestException('Maximum number of metadata definitions reached');
        }

        $metadataDefinition = $this->subscriberMetadataService->createMetadataDefinition(
            $newsletter,
            $input->key,
            $input->name,
        );

        return $this->json(new SubscriberMetadataDefinitionObject($metadataDefinition));
    }

    #[Route('/subscriber-metadata-definitions/{id}', methods: 'PATCH')]
    #[ScopeRequired(PostScope::SUBSCRIBERS_WRITE)]
    #[OA\Patch(
        description: 'Updates the name of a subscriber metadata definition.',
        summary: 'Update a subscriber metadata definition',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated subscriber metadata definition object.',
        content: new Model(type: SubscriberMetadataDefinitionObject::class),
    )]
    public function updateMetadata(
        SubscriberMetadataDefinition $metadataDefinition,
        #[MapRequestPayload] UpdateSubscriberMetadataDefinitionInput $input,
    ): JsonResponse {
        $this->subscriberMetadataService->updateMetadataDefinition($metadataDefinition, $input->name);
        return $this->json(new SubscriberMetadataDefinitionObject($metadataDefinition));
    }

    #[Route('/subscriber-metadata-definitions/{id}', methods: 'DELETE')]
    #[ScopeRequired(PostScope::SUBSCRIBERS_WRITE)]
    #[OA\Delete(
        description: 'Deletes a subscriber metadata definition. Subscriber metadata values for this key are also removed.',
        summary: 'Delete a subscriber metadata definition',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns an empty object on success.',
        content: new OA\JsonContent(),
    )]
    public function deleteMetadata(SubscriberMetadataDefinition $metadataDefinition): JsonResponse
    {
        $this->subscriberMetadataService->deleteMetadataDefinition($metadataDefinition);
        return $this->json([]);
    }

}
