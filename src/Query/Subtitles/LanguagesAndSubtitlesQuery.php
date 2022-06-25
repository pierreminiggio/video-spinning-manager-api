<?php

namespace App\Subtitles;

use App\Entity\Subtitles\LanguagesAndSubtitles;
use App\Query\QueryWithIdParameter;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class LanguagesAndSubtitlesQuery implements QueryWithIdParameter
{
    public function __construct(private DatabaseFetcher $fetcher, private string $token)
    {
    }

    public function execute(int $id): ?LanguagesAndSubtitles
    {
        $querieds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content as sc'
            )->select(
                'sc.id as id, yv.youtube_id as youtube_id'
            )->join(
                'spinned_content_youtube_video as scyv',
                'sc.id = scyv.spinned_id'
            )->join(
                'youtube_video as yv',
                'yv.id = scyv.youtube_id'
            )
            ->where(
                'sc.id = :id'
            ),
            ['id' => $id]
        );
        
        if (! $querieds) {
            return null;
        }

        $queried = $querieds[0];
        $youtubeId = $queried['youtube_id'];

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
            http_response_code(401);
            exit;
        }

        if ($httpCode !== 200) {
            http_response_code(500);
            exit;
        }

        if (! $subtitlesResponse) {
            http_response_code(500);
            exit;
        }

        $subtitlesJsonResponse = json_decode($subtitlesResponse, true);

        if (! $subtitlesJsonResponse) {
            http_response_code(500);
            exit;
        }

        var_dump($subtitlesJsonResponse); die;
    }
}
