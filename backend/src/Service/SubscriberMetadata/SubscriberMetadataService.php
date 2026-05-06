<?php

namespace App\Service\SubscriberMetadata;

use App\Entity\Newsletter;
use App\Entity\SubscriberMetadataDefinition;
use App\Entity\Type\SubscriberMetadataDefinitionType;
use App\Service\SubscriberMetadata\Exception\MetadataValidationFailedException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SubscriberMetadataService
{

    public const int MAX_METADATA_DEFINITIONS_PER_NEWSLETTER = 20;

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @return SubscriberMetadataDefinition[]
     */
    public function getMetadataDefinitions(Newsletter $newsletter): array
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->findBy(['newsletter' => $newsletter]);
    }

    public function getMetadataDefinitionByKey(Newsletter $newsletter, string $key): ?SubscriberMetadataDefinition
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->findOneBy(['newsletter' => $newsletter, 'key' => $key]);
    }

    /**
     * @param string[] $keys
     * @return SubscriberMetadataDefinition[]
     */
    public function getMetadataDefinitionsByKeys(Newsletter $newsletter, array $keys): array
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->findBy(['newsletter' => $newsletter, 'key' => $keys]);
    }

    public function getMetadataDefinitionsCount(Newsletter $newsletter): int
    {
        return $this->entityManager
            ->getRepository(SubscriberMetadataDefinition::class)
            ->count(['newsletter' => $newsletter]);
    }

    public function createMetadataDefinition(
        Newsletter $newsletter,
        string $key,
        string $name,
    ): SubscriberMetadataDefinition {
        $metadataDefinition = new SubscriberMetadataDefinition();
        $metadataDefinition->setNewsletter($newsletter);
        $metadataDefinition->setKey($key);
        $metadataDefinition->setName($name);
        $metadataDefinition->setType(SubscriberMetadataDefinitionType::TEXT);
        $metadataDefinition->setCreatedAt($this->now());
        $metadataDefinition->setUpdatedAt($this->now());

        $this->entityManager->persist($metadataDefinition);
        $this->entityManager->flush();

        return $metadataDefinition;
    }

    public function updateMetadataDefinition(
        SubscriberMetadataDefinition $metadataDefinition,
        string $name,
    ): void {
        $metadataDefinition->setName($name);
        $metadataDefinition->setUpdatedAt($this->now());

        $this->entityManager->flush();
    }

    public function deleteMetadataDefinition(SubscriberMetadataDefinition $metadataDefinition): void
    {
        $this->entityManager->remove($metadataDefinition);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, scalar> $metadata
     * @throws MetadataValidationFailedException
     */
    public function validateMetadata(Newsletter $newsletter, array $metadata): void
    {
        $keys = array_keys($metadata);
        $definitions = $this->getMetadataDefinitionsByKeys($newsletter, $keys);

        if (count($definitions) !== count($keys)) {
            $foundKeys = array_map(fn(SubscriberMetadataDefinition $def) => $def->getKey(), $definitions);
            $missingKeys = array_diff($keys, $foundKeys);
            throw new MetadataValidationFailedException(
                "Metadata definitions with keys " . implode(', ', $missingKeys) . " not found",
            );
        }

        foreach ($definitions as $definition) {
            $value = $metadata[$definition->getKey()] ?? null;
            if (!$this->validateValueType($definition, $value)) {
                throw new MetadataValidationFailedException(
                    "Invalid value type for metadata key " . $definition->getKey(
                    ) . ". Expected type: " . $definition->getType()->toJsonType(),
                );
            }
        }
    }

    private function validateValueType(
        SubscriberMetadataDefinition $metadataDefinition,
        mixed $value,
    ): bool {
        return match ($metadataDefinition->getType()) {
            //  @phpstan-ignore-next-line
            SubscriberMetadataDefinitionType::TEXT => is_string($value),
            // Other Metadata types can be added here
            default => false,
        };
    }
}
