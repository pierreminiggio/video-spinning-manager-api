<?php

use App\Query\Video\TikTokVideosToUploadQuery;
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

$query = new TikTokVideosToUploadQuery($fetcher);
$tikTokIdsToUpload = $query->execute();

foreach ($tikTokIdsToUpload as $tikTokIdToUpload) {
    // TODO recup tiktok infos
    // TODO recup upload status infos
    // TODO recup render status infos

    // TODO recup account infos
    // TODO upload
    // TODO success/failed
}
