<?php

namespace App\Normalizer;

use App\Entity\Video\VideoDetail;

class VideoDetailNormalizer implements FocusedNormalizerInterface
{

    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function supportsNormalization(mixed $entity): bool
    {
        return $entity instanceof VideoDetail;
    }

    public function normalize(mixed $entity): array
    {
        /** @var VideoDetail $entity */
        $normalizedEntity = $this->normalize($entity);
        $normalizedEntity['video'] = $this->normalize($entity->video);

        return $normalizedEntity;
    }
}