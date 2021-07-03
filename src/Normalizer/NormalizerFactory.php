<?php

namespace App\Normalizer;

class NormalizerFactory
{
    public function make(): NormalizerInterface
    {
        $defaultNormalizer = new DefaultNormalizer();
        $videoNormalizer = new VideoNormalizer(new Normalizer([$defaultNormalizer]));
        $socialMediaAccountNormalizer = new SocialMediaAccountNormalizer(new Normalizer([$defaultNormalizer]));
        $accountCollectionNormalizer = new AccountCollectionNormalizer(new Normalizer([
            $socialMediaAccountNormalizer,
            $defaultNormalizer
        ]));

        return new Normalizer([
            new VideoDetailNormalizer(new Normalizer([
                $videoNormalizer,
                $accountCollectionNormalizer,
                $defaultNormalizer
            ])),
            $videoNormalizer,
            $defaultNormalizer
        ]);
    }
}