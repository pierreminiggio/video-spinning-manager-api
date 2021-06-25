<?php

namespace App\Normalizer;

use App\Entity\Video\Video;
use DateTimeInterface;

class VideoNormalizer implements FocusedNormalizerInterface
{

    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function supportsNormalization(mixed $entity): bool
    {
        return $entity instanceof Video;
    }

    public function normalize(mixed $entity): array
    {
        /** @var Video $entity */
        $finishedAt = $entity->finishedAt;

        $normalizedEntity = $this->normalizer->normalize($entity);

        $normalizedEntity['finishedAt'] = $finishedAt?->format(DateTimeInterface::ATOM)
        ;

        return $normalizedEntity;
    }
}