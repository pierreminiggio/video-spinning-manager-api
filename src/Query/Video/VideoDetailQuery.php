<?php

namespace App\Query\Video;

use App\Entity\Video\EditorState;
use App\Entity\Video\Video;
use App\Entity\Video\VideoDetail;
use App\Query\Query;
use DateTime;
use NeutronStars\Database\Query as DatabaseQuery;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class VideoDetailQuery implements Query
{
    public function __construct(
        private DatabaseFetcher $fetcher,
        private string $cacheFolder
    )
    {
    }

    public function execute(int $id): ?VideoDetail
    {
        $querieds = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video'
            )->select(
                'id, content_id, name, width, height, finished_at'
            )->where(
                'id = :id'
            ),
            ['id' => $id]
        );
        
        if (! $querieds) {
            return null;
        }

        $queried = $querieds[0];
        $videoId = (int) $queried['id'];
        $finishedAtString = $queried['finished_at'];
        $video = new Video(
            $videoId,
            $queried['name'],
            (int) $queried['width'],
            (int) $queried['height'],
            $finishedAtString ? new DateTime($finishedAtString) : null
        );
        
        $queriedEditorStates = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_video_editor_state'
            )->select(
                'clips, texts'
            )->where(
                'video_id = :video_id'
            )->orderBy(
                'created_at',
                DatabaseQuery::ORDER_BY_DESC
            )->limit(
                1
            ),
            ['video_id' => $videoId]
        );
        
        $editorState = new EditorState([], []);
        
        if ($queriedEditorStates) {
            $queriedEditorState = $queriedEditorStates[0];
            $editorState->clips = json_decode($queriedEditorState['clips'], true);
            $editorState->texts = json_decode($queriedEditorState['texts'], true);
        }

        return new VideoDetail($video, file_exists($this->cacheFolder . (int) $queried['content_id'] . '.mp4'), $editorState);
    }
}
