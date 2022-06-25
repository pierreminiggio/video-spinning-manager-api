<?php

namespace App\Query\Subtitles;

use App\Entity\Subtitles\LanguageAndSubtitles;
use App\Entity\Subtitles\LanguagesAndSubtitles;
use App\Entity\Subtitles\Subtitles;
use App\Query\QueryWithIdParameter;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;
use RuntimeException;

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
        }, $subtitlesJsonResponse)));
    }
}
