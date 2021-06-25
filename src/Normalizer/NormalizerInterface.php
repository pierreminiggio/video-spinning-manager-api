<?php

namespace App\Normalizer;

interface NormalizerInterface
{
    public function normalize(mixed $entity): array;
}