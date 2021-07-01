<?php

use App\Query\Account\TikTok\AccountQuery;
use App\Query\Render\CurrentRenderStatusForVideoQuery;
use App\Query\Video\TikTok\CurrentUploadStatusForTiKTokQuery;
use App\Query\Video\TikTok\TikTokUploadQuery;
use App\Query\Video\TikTok\VideosToUploadQuery;
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

$query = new VideosToUploadQuery($fetcher);
$tikTokIdsToUpload = $query->execute();

$tikTokUploadQuery = new TikTokUploadQuery($fetcher);
$tikTokUploadStatusQuery = new CurrentUploadStatusForTiKTokQuery($fetcher);
$currentRenderStatusQuery = new CurrentRenderStatusForVideoQuery($fetcher);
$accountQuery = new AccountQuery($fetcher);

foreach ($tikTokIdsToUpload as $tikTokIdToUpload) {
    $tiktok = $tikTokUploadQuery->execute($tikTokIdToUpload);

    if ($tiktok === null) {
        continue; // wtf
    }

    $uploadStatus = $tikTokUploadStatusQuery->execute($tikTokIdToUpload);

    $isAlreadyUploading = $uploadStatus !== null && $uploadStatus->failedAt === null;
    if ($isAlreadyUploading) {
        continue;
    }

    $videoId = $tiktok->videoId;

    $renderStatus = $currentRenderStatusQuery->execute($videoId);

    if ($renderStatus === null || $renderStatus->finishedAt === null) {
        continue; // not ready to be uploaded
    }

    $accountId = $tiktok->accountId;
    $account = $accountQuery->execute($accountId);

    if ($account === null) {
        continue; // wtf
    }

    // TODO create upload status
    // TODO upload
    // TODO success/fail added to upload status
}
