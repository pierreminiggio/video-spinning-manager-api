<?php

namespace App\Query\Subtitles;

use App\Denormalizer\LanguagesAndSubtitlesDenormalizer;
use App\Entity\Subtitles\LanguagesAndSubtitles;
use App\Query\Content\YoutubeIdQuery;
use App\Query\QueryWithIdParameter;
use RuntimeException;

class LanguagesAndSubtitlesQuery implements QueryWithIdParameter
{
    public function __construct(
        private YoutubeIdQuery $youtubeIdQuery,
        private string $token,
        private LanguagesAndSubtitlesDenormalizer $denormalizer
    )
    {
    }

    public function execute(int $id): ?LanguagesAndSubtitles
    {
        $youtubeId = $this->youtubeIdQuery->execute($id);

        if (! $youtubeId) {
            return null;
        }

        $subtitlesCurl = curl_init('https://youtube-subtitles.ggio.fr/' . $youtubeId);
        curl_setopt_array($subtitlesCurl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json' , 'Authorization: Bearer ' . $this->token]
        ]);
        $subtitlesResponse = curl_exec($subtitlesCurl);
        $httpCode = curl_getinfo($subtitlesCurl)['http_code'];
        curl_close($subtitlesCurl);

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
