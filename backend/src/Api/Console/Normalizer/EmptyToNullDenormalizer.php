<?php

namespace App\Api\Console\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;

class EmptyToNullDenormalizer implements DenormalizerInterface
{

    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private readonly ObjectNormalizer $normalizer
    )
    {
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): mixed {

        $value = $this->normalizer->denormalize($data, $type, $format, $context);

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        dd($classMetadataFactory->getMetadataFor($value));

        return $data;

    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool
    {
        dump($context);
//        $classReflection = new \ReflectionClass($data);
//        dump($classReflection->getAttributes(NormalizeEmptyToNull::class));
        return true;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }

}