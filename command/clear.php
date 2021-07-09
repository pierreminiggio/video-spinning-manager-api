<?php

use App\Query\Clear\DoneVideoQuery;
use PierreMiniggio\ConfigProvider\ConfigProvider;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

$projectDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

require $projectDir . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

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

$cacheDir = $projectDir . 'cache' . DIRECTORY_SEPARATOR;

$doneVideoQuery = new DoneVideoQuery($fetcher);

$doneVideos = $doneVideoQuery->execute();

$cacheContent = scandir($cacheDir);

/** @var int[] $clearedContents */
$clearedContents = [];

foreach ($doneVideos as $doneVideo) {

    $contentId = $doneVideo->contentId;

    if (in_array($contentId, $clearedContents)) {
        continue;
    }

    $videoPath = $cacheDir . $contentId . '.mp4';

    if (file_exists($videoPath)) {
        unlink($videoPath);
    }

    $thumbnails = array_filter(
        $cacheContent,
        fn (string $thumbnail) => str_starts_with(
            $thumbnail,
            $contentId . '-'
        ) && str_ends_with(
            $thumbnail,
            '.png'
        )
    );

    foreach ($thumbnails as $thumbnail) {
        $thumbnailPath = $cacheDir . $thumbnail;
        unlink($thumbnailPath);
    }

    $clearedContents[] = $contentId;
}
