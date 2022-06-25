<?php

namespace App\Normalizer;

class DefaultNormalizer implements FocusedNormalizerInterface
{

    public function supportsNormalization(mixed $entity): bool
    {
        return true;
    }

    public function normalize(mixed $entity): array
    {
        return json_decode(json_encode($entity), true);
    }
}
