<?php

namespace App\Normalizer;

use App\Entity\Account\AccountCollection;
use App\Entity\Account\SocialMediaAccount;
use App\Enum\UploadTypeEnum;

class AccountCollectionNormalizer implements FocusedNormalizerInterface
{

    public function __construct(private NormalizerInterface $normalizer)
    {
    }

    public function supportsNormalization(mixed $entity): bool
    {
        return $entity instanceof AccountCollection;
    }

    public function normalize(mixed $entity): array
    {
        /** @var AccountCollection $entity */

        $normalizedEntity = [];

        $socialMediaTypes = [UploadTypeEnum::TIKTOK];

        foreach ($socialMediaTypes as $socialMediaType) {
            /** @var SocialMediaAccount[] $socialMediaAccounts */
            $socialMediaAccounts = $entity->{$socialMediaType};
            $normalizedEntity[$socialMediaType] = array_map(
                fn (SocialMediaAccount $socialMediaAccount): array => $this->normalizer->normalize($socialMediaAccount),
                $socialMediaAccounts
            );
        }
    }
}