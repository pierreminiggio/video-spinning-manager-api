<?php

namespace App\Normalizer;

use App\Entity\Subtitles\LanguageAndSubtitles;
use App\Entity\Subtitles\LanguagesAndSubtitles;
use App\Entity\Subtitles\Subtitles;
use RuntimeException;

class LanguagesAndSubtitlesNormalizer implements FocusedNormalizerInterface
{
    public function supportsNormalization(mixed $entity): bool
    {
        return $entity instanceof LanguagesAndSubtitles;
    }

    public function normalize(mixed $entity): array
    {
        if (! $entity instanceof LanguagesAndSubtitles) {
            throw new RuntimeException('Unsupported entity');
        }

        return array_map(fn (LanguageAndSubtitles $languageAndSubtitles): array => [
            'language' => $languageAndSubtitles->getLanguage(),
            'subtitles' => array_map(fn (Subtitles $subtitles): array => [
                'startTime' => $subtitles->getStartTime(),
                'endTime' => $subtitles->getEndTime(),
                'text' => $subtitles->getText()
            ], $languageAndSubtitles->getSubtitles())
        ], $entity->getLanguagesAndSubtitles());
    }
}
