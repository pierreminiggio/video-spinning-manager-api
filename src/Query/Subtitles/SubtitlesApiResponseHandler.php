<?php

namespace App\Query\Subtitles;

use App\Denormalizer\LanguagesAndSubtitlesDenormalizer;
use App\Entity\Subtitles\LanguagesAndSubtitles;
use RuntimeException;

class SubtitlesApiResponseHandler
{
    public function __construct(
        private LanguagesAndSubtitlesDenormalizer $denormalizer
    )
    {}

    public function handle(int $httpCode, string | bool $subtitlesResponse): ?LanguagesAndSubtitles
    {
        if ($httpCode === 404) {
            return null;
        }

        if ($httpCode === 401) {
            throw new RuntimeException('Unauthorized');
        }

        if ($httpCode !== 200) {
            throw new RuntimeException('Server error : Bad HTTP code');
        }

        if (! $subtitlesResponse) {
            throw new RuntimeException('Server error : Emptu subtitles response');
        }

        $subtitlesJsonResponse = json_decode($subtitlesResponse, true);

        if (! $subtitlesJsonResponse) {
            throw new RuntimeException('Server error : Bad subtitles JSON response');
        }

        return $this->denormalizer->denormalize($subtitlesJsonResponse);
    }
}
