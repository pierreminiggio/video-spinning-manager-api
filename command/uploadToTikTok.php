<?php

use App\Command\Social\MarkAsFailedCommand;
use App\Command\Social\MarkAsFinishedCommand;
use App\Command\Social\MarkAsUploadingCommand;
use App\Enum\UploadTypeEnum;
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
$url = $config['url'];

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
$markAsUploadingCommand = new MarkAsUploadingCommand($fetcher);

$markAsFailedCommand = new MarkAsFailedCommand($fetcher);

$markAsFailed = function (int $tikTokId, string $failReason) use ($markAsFailedCommand): void {
    $markAsFailedCommand->execute(UploadTypeEnum::TIKTOK, $tikTokId, $failReason);
};

$markAsFinishedCommand = new MarkAsFinishedCommand($fetcher);

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

    if (! $renderStatus->hasRenderedFile()) {
        continue; // wtf happened
    }

    $accountId = $tiktok->accountId;
    $account = $accountQuery->execute($accountId);

    if ($account === null) {
        continue; // wtf
    }

    $markAsUploadingCommand->execute(UploadTypeEnum::TIKTOK, $tikTokIdToUpload);

    //$curl = curl_init($account->url);
    $curl = curl_init('https://tiktok-poster-api.ggio.fr/iluminati312');

    $authHeader = ['Content-Type: application/json' , 'Authorization: Bearer ' . $account->token];
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $authHeader,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => json_encode([
            'video_url' => $url . '/render/' . $videoId,
            'legend' => $tiktok->legend
        ])
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpCode !== 200) {
        $markAsFailed(
            $tikTokIdToUpload,
            'API returned bad http code : ' . $httpCode . ', response : ' . $response
        );
        continue;
    }

    if (! $response) {
        $markAsFailed($tikTokIdToUpload, 'API returned an empty response');
        continue;
    }

    $jsonResponse = json_decode($response, true);

    if (! $jsonResponse) {
        $markAsFailed($tikTokIdToUpload, 'API returned a bad json response : ' . $response);
        continue;
    }

    if (empty($jsonResponse['url'])) {
        $markAsFailed($tikTokIdToUpload, 'API returned a bad json response, "url" missing : ' . $jsonResponse);
        continue;
    }

    $remoteUrl = $jsonResponse['url'];

    $markAsFinishedCommand->execute(UploadTypeEnum::TIKTOK, $tikTokIdToUpload, $remoteUrl);
}

