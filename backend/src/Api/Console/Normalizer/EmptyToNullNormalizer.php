<?php

namespace App\Api\Console\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EmptyToNullNormalizer implements DenormalizerInterface
{

    public function __construct(
        #[Autowire(service: 'serializer.denormalizer.array')]
        private readonly DenormalizerInterface $denormalizer,
    )
    {
    }

    public function denormalize(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|\ArrayObject|null {

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $data[$key] = null;
            } elseif (is_array($value)) {
                $data[$key] = $this->denormalize($value, $type, $format, $context);
            }
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);

    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = []
    ): bool
    {
        dump($data);
//        $classReflection = new \ReflectionClass($data);
//        dump($classReflection->getAttributes(NormalizeEmptyToNull::class));
        return true;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }

}