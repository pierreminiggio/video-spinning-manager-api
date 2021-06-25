<?php

namespace App\Normalizer;

class NormalizerFactory
{
    public function make(): NormalizerInterface
    {
        $defaultNormalizer = new DefaultNormalizer();

        return new Normalizer([
            new VideoNormalizer($defaultNormalizer),
            $defaultNormalizer
        ]);
    }
}