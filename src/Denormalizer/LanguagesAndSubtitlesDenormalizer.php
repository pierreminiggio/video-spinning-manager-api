<?php

namespace App\Denormalizer;

use App\Entity\Subtitles\LanguageAndSubtitles;
use App\Entity\Subtitles\LanguagesAndSubtitles;
use App\Entity\Subtitles\Subtitles;

class LanguagesAndSubtitlesDenormalizer
{
    public function denormalize(array $data): LanguagesAndSubtitles
    {
        return new LanguagesAndSubtitles(array_filter(array_map(function (array $entry): ?LanguageAndSubtitles {
            if (! isset($entry['language'], $entry['subtitles'])) {
                return null;
            }

            return new LanguageAndSubtitles($entry['language'], array_filter(
                array_map(function (array $subtitlesEntry): ?Subtitles {
                    if (! isset($subtitlesEntry['startTime'], $subtitlesEntry['endTime'], $subtitlesEntry['text'])) {
                        return null;
                    }

                    return new Subtitles(
                        $subtitlesEntry['startTime'],
                        $subtitlesEntry['endTime'],
                        $subtitlesEntry['text']
                    );
                }, $entry['subtitles'])
            ));
        }, $data)));
    }
}
