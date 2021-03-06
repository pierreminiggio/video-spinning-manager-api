<?php

use App\Command\Render\MarkAsFailedCommand;
use App\Command\Render\MarkAsFinishedCommand;
use App\Command\Render\MarkAsRenderingCommand;
use App\Entity\Render\VideoConfig;
use App\Query\Editor\CurrentStateQuery;
use App\Query\Render\CurrentRenderStatusForVideoQuery;
use App\Query\Render\VideoConfigQuery;
use App\Query\Video\VideosToRenderQuery;
use PierreMiniggio\ConfigProvider\ConfigProvider;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;
use PierreMiniggio\GithubActionRemotionRenderer\GithubActionRemotionRenderer;
use PierreMiniggio\GithubActionRemotionRenderer\GithubActionRemotionRendererException;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$configProvider = new ConfigProvider(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
$config = $configProvider->get();

$dbConfig = $config['db'];
$fetcher = new DatabaseFetcher(new DatabaseConnection(
    $dbConfig['host'],
    $dbConfig['database'],
    $dbConfig['username'],
    $dbConfig['password'],
    DatabaseConnection::UTF8_MB4
));

$query = new VideosToRenderQuery($fetcher);
$videoIdsToRender = $query->execute();

$currentRenderStatusQuery = new CurrentRenderStatusForVideoQuery($fetcher);
$markAsRenderingCommand = new MarkAsRenderingCommand($fetcher);

$currentEditorStateQuery = new CurrentStateQuery($fetcher);
$videoConfigQuery = new VideoConfigQuery($fetcher);

$renderer = new GithubActionRemotionRenderer();
$rendererProjects = $config['rendererProjects'];

$markAsFailedCommand = new MarkAsFailedCommand($fetcher);
$markAsFinishedCommand = new MarkAsFinishedCommand($fetcher);

/** @var array<int, VideoConfig|null> $videoConfigs */
$videoConfigs = [];

foreach ($videoIdsToRender as $videoIdToRender) {
    $renderStatus = $currentRenderStatusQuery->execute($videoIdToRender);

    $isAlreadyRendering = $renderStatus !== null && $renderStatus->failedAt === null;
    if ($isAlreadyRendering) {
        continue;
    }

    $props = $currentEditorStateQuery->execute($videoIdToRender);

    if ($props === null) {
        continue;
    }

    if (! isset($videoConfigs[$videoIdToRender])) {
        $videoConfigs[$videoIdToRender] = $videoConfigQuery->execute($videoIdToRender);
    }

    $videoConfig = $videoConfigs[$videoIdToRender];

    if ($videoConfig === null) {
        // No config for video ? That's odd
        continue;
    }

    $rendererProject = $rendererProjects[array_rand($rendererProjects)];

    $durationInFrames = 0;
    $jsonProps = json_decode($props, true);

    if (! isset($jsonProps['clips'])) {
        // no clip ? :o
        continue;
    }

    $clips = $jsonProps['clips'];

    foreach ($clips as $clip) {
        if (! isset($clip['durationInFrames'])) {
            // wtf
            continue;
        }

        $durationInFrames += (int) $clip['durationInFrames'];
    }

    $markAsRenderingCommand->execute($videoIdToRender);
    $renderStatus = $currentRenderStatusQuery->execute($videoIdToRender);

    if ($renderStatus === null) {
        // Mark as rendering failed ?
        continue;
    }

    try {
        $videoFile = $renderer->render(
            $rendererProject['token'],
            $rendererProject['account'],
            $rendererProject['project'],
            300,
            1,
            [
                'props' => $props,
                'width' => (string) $videoConfig->width,
                'height' => (string) $videoConfig->height,
                'fps' => (string) $videoConfig->fps,
                'durationInFrames' => (string) $durationInFrames
            ]
        );
    } catch (GithubActionRemotionRendererException $e) {
        $markAsFailedCommand->execute(
            $renderStatus->id,
            json_encode([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ])
        );
        continue;
    }

    $markAsFinishedCommand->execute($renderStatus->id, $videoFile);
}
