<?php

namespace App\Normalizer;

use App\Entity\Account\SocialMediaAccount;

class SocialMediaAccountNormalizer implements FocusedNormalizerInterface
{

    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function supportsNormalization(mixed $entity): bool
    {
        return $entity instanceof SocialMediaAccount;
    }

    public function normalize(mixed $entity): array
    {
        /** @var SocialMediaAccount $entity */
        $normalizedEntity = $this->normalizer->normalize($entity);
        $normalizedEntity['predictedNextPostTime'] = $entity->predictedNextPostTime?->format('Y-m-d H:i:s');

        return $normalizedEntity;
    }
}