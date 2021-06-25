<?php

namespace App\Serializer;

use App\Normalizer\NormalizerInterface;

class Serializer implements SerializerInterface
{

    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function serialize(mixed $entity): string
    {
        return json_encode($this->normalizer->normalize($entity));
    }
}