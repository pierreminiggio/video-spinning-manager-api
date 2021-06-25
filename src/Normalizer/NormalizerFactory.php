<?php

namespace App\Normalizer;

class NormalizerFactory
{
    public function make(): NormalizerInterface
    {
        $defaultNormalizer = new DefaultNormalizer();
        $videoNormalizer = new VideoNormalizer(new Normalizer([$defaultNormalizer]));

        return new Normalizer([
            new VideoDetailNormalizer(new Normalizer([$videoNormalizer, $defaultNormalizer])),
            $videoNormalizer,
            $defaultNormalizer
        ]);
    }
}