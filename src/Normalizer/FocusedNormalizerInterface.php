<?php

namespace App\Normalizer;

interface FocusedNormalizerInterface extends NormalizerInterface
{
    public function supportsNormalization(mixed $entity): bool;
}
