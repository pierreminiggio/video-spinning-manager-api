<?php

use App\Command\Render\MarkAsFailedCommand;
use App\Command\Render\MarkAsRenderingCommand;
use App\Query\Editor\CurrentStateQuery;
use App\Query\Render\CurrentRenderStatusForVideoQuery;
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

$renderer = new GithubActionRemotionRenderer();
$rendererProjects = $config['rendererProjects'];

$markAsFailedCommand = new MarkAsFailedCommand($fetcher);

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

    $markAsRenderingCommand->execute($videoIdToRender);
    $renderStatus = $currentRenderStatusQuery->execute($videoIdToRender);

    if ($renderStatus === null) {
        // Mark as rendering failed ?
        continue;
    }

    $rendererProject = $rendererProjects[array_rand($rendererProjects)];

    try {
        $videoFile = $renderer->render(
            $rendererProject['token'],
            $rendererProject['account'],
            $rendererProject['project'],
            30,
            1,
            [
                'props' => $props
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

    var_dump($videoFile);
    // v TODO REMOVE TEST COMMENT v
    break;
}