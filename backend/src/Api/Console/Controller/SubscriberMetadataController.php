<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\SubscriberMetadata\CreateSubscriberMetadataDefinitionInput;
use App\Api\Console\Input\SubscriberMetadata\UpdateSubscriberMetadataDefinitionInput;
use App\Api\Console\Object\SubscriberMetadataDefinitionObject;
use App\Entity\Newsletter;
use App\Entity\SubscriberMetadataDefinition;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class SubscriberMetadataController extends AbstractController
{

    public function __construct(
        private SubscriberMetadataService $subscriberMetadataService
    ) {
    }

    #[Route('/subscriber-metadata-definitions', methods: 'POST')]
    public function createMetadata(
        Newsletter $newsletter,
        #[MapRequestPayload] CreateSubscriberMetadataDefinitionInput $input
    ): JsonResponse {
        $current = $this->subscriberMetadataService->getMetadataDefinitionByKey($newsletter, $input->key);

        if ($current) {
            throw new BadRequestException('Key already exists');
        }

        $count = $this->subscriberMetadataService->getMetadataDefinitionsCount($newsletter);

        if ($count >= SubscriberMetadataService::MAX_METADATA_DEFINITIONS_PER_PROJECT) {
            throw new BadRequestException('Maximum number of metadata definitions reached');
        }

        $metadataDefinition = $this->subscriberMetadataService->createMetadataDefinition(
            $newsletter,
            $input->key,
            $input->name
        );

        return $this->json(new SubscriberMetadataDefinitionObject($metadataDefinition));
    }

    #[Route('/subscriber-metadata-definitions/{id}', methods: 'PATCH')]
    public function updateMetadata(
        SubscriberMetadataDefinition $metadataDefinition,
        #[MapRequestPayload] UpdateSubscriberMetadataDefinitionInput $input
    ): JsonResponse {
        $this->subscriberMetadataService->updateMetadataDefinition($metadataDefinition, $input->name);
        return $this->json(new SubscriberMetadataDefinitionObject($metadataDefinition));
    }

    #[Route('/subscriber-metadata-definitions/{id}', methods: 'DELETE')]
    public function deleteMetadata(SubscriberMetadataDefinition $metadataDefinition): JsonResponse
    {
        $this->subscriberMetadataService->deleteMetadataDefinition($metadataDefinition);
        return $this->json([]);
    }

}