<?php

namespace App\Service\SubscriberMetadata;

use App\Entity\Project;
use App\Entity\SubscriberMetadataDefinition;
use Doctrine\ORM\EntityManagerInterface;

class SubscriberMetadataService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return SubscriberMetadataDefinition[]
     */
    public function getMetadataDefinitions(Project $project): array
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->findBy(['project' => $project]);
    }

}