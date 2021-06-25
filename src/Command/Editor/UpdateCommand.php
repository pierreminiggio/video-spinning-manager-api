<?php

namespace App\Command\Editor;

use Exception;
use NeutronStars\Database\Query;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class UpdateCommand
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    /**
     * @throws Exception
     */
    public function execute(
        int $videoId,
        array $clips,
        array $texts,
        array $clipMakerProps
    ): void
    {
        $this->fetcher->exec(
            $this->fetcher->createQuery(
                'spinned_content_video_editor_state'
            )->insertInto(
                'video_id,clips,texts,clip_maker_props',
                ':video_id,:clips,:texts,:clip_maker_props'
            ),
            [
                'video_id' => $videoId,
                'clips' => $clips,
                'texts' => $texts,
                'clip_maker_props' => $clipMakerProps
            ]
        );
    }
}
