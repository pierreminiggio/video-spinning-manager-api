<?php

namespace App\Query\Subtitles;

use App\Entity\Subtitles\LanguagesAndSubtitles;
use App\Query\Content\YoutubeIdQuery;
use App\Query\QueryWithIdParameter;

class LanguagesAndSubtitlesUpdateQuery implements QueryWithIdParameter
{
    public function __construct(
        private YoutubeIdQuery $youtubeIdQuery,
        private string $token,
        private SubtitlesApiResponseHandler $responseHandler
    )
    {
    }

    public function execute(int $id): ?LanguagesAndSubtitles
    {
        $youtubeId = $this->youtubeIdQuery->execute($id);

        if (! $youtubeId) {
            return null;
        }

        set_time_limit(720);
        $subtitlesCurl = curl_init('https://youtube-subtitles.ggio.fr/' . $youtubeId);
        curl_setopt_array($subtitlesCurl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json' , 'Authorization: Bearer ' . $this->token],
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 660
        ]);
        $subtitlesResponse = curl_exec($subtitlesCurl);
        $httpCode = curl_getinfo($subtitlesCurl)['http_code'];
        curl_close($subtitlesCurl);

        return $this->responseHandler->handle($httpCode, $subtitlesResponse);
    }
}
