<?php

namespace App\Normalizer;

use RuntimeException;

class Normalizer implements NormalizerInterface
{

    /** @var FocusedNormalizerInterface[] */
    public array $normalizers;

    /**
     * @param FocusedNormalizerInterface[] $normalizers
     */
    public function __construct(array $normalizers)
    {
        $this->normalizers = $normalizers;
    }

    /**
     * @throws RuntimeException
     */
    public function normalize(mixed $entity): array
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supportsNormalization($entity)) {
                return $normalizer->normalize($entity);
            }
        }

        throw new RuntimeException('Normalizer not found');
    }
}