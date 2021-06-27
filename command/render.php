<?php

use App\Command\Render\MarkAsRenderingCommand;
use App\Query\Render\CurrentRenderStatusForVideoQuery;
use App\Query\Video\VideosToRenderQuery;
use PierreMiniggio\ConfigProvider\ConfigProvider;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

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

foreach ($videoIdsToRender as $videoIdToRender) {
    $renderStatus = $currentRenderStatusQuery->execute($videoIdToRender);

    $isAlreadyRendering = $renderStatus !== null && $renderStatus->failedAt === null;
    if ($isAlreadyRendering) {
        continue;
    }

    if ($renderStatus === null) {
        $markAsRenderingCommand->execute($videoIdToRender);
        $renderStatus = $currentRenderStatusQuery->execute($videoIdToRender);
    }

    if ($renderStatus === null) {
        // Mark as rendering failed ?
        continue;
    }

    var_dump($renderStatus);
}
