<?php

namespace App;

use App\Command\Editor\UpdateCommand;
use App\Command\EndProcessCommand;
use App\Command\Social\MarkAsFinishedCommand;
use App\Command\Social\MarkAsUploadingCommand;
use App\Command\Social\TikTok\PostCommand;
use App\Command\Video\CreateCommand;
use App\Command\Video\FinishCommand;
use App\Controller\DetailController;
use App\Controller\DownloaderController;
use App\Controller\Editor\Text\Preset\ListController;
use App\Controller\Editor\UpdateController;
use App\Controller\Render\DisplayController;
use App\Controller\Social\TikTok\PostController;
use App\Controller\Social\TikTok\VideoFileController;
use App\Controller\Video\CreateController;
use App\Controller\EndProcessController;
use App\Controller\Social\ToUploadListController;
use App\Controller\Social\NonUploadedToUploadingController;
use App\Controller\Social\UploadingToUploadedController;
use App\Controller\ThumbnailController;
use App\Controller\ToProcessListController;
use App\Controller\Video\FinishController;
use App\Denormalizer\LanguagesAndSubtitlesDenormalizer;
use App\Http\Request\JsonBodyParser;
use App\Normalizer\NormalizerFactory;
use App\Query\Account\PostedOnAccountsQuery;
use App\Query\Account\SocialMediaAccountsByContentQuery;
use App\Query\Account\TikTok\CanVideoBePostedOnThisTikTokAccountQuery;
use App\Query\Account\TikTok\PredictedNextPostTimeQuery;
use App\Query\Account\TikTok\VideoFileQuery;
use App\Query\Content\ToProcessDetailQuery;
use App\Query\Content\ToProcessListQuery;
use App\Query\Content\VideoLinkQuery;
use App\Query\Content\YoutubeIdQuery;
use App\Query\Editor\Preset\ListQuery;
use App\Query\Render\CurrentRenderStatusForVideoQuery;
use App\Query\Subtitles\LanguagesAndSubtitlesQuery;
use App\Query\Subtitles\LanguagesAndSubtitlesUpdateQuery;
use App\Query\Subtitles\SubtitlesApiResponseHandler;
use App\Query\Video\TikTok\CurrentUploadStatusForTikTokQuery;
use App\Query\Video\TikTok\TikTokUploadQuery;
use App\Query\Video\TikTok\VideosToUploadQuery;
use App\Query\Video\VideoDetailQuery;
use App\Serializer\Serializer;
use App\Serializer\SerializerInterface;
use Exception;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;
use PierreMiniggio\MP4YoutubeVideoDownloader\Downloader;
use PierreMiniggio\MP4YoutubeVideoDownloader\Repository;
use RuntimeException;

class App
{
    public function run(
        string $path,
        ?string $queryParameters,
        ?string $authHeader,
        ?string $origin,
        ?string $accessControlRequestHeaders
    ): void
    {

        if ($origin) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }

        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

        if ($accessControlRequestHeaders) {
            header('Access-Control-Allow-Headers: ' . $accessControlRequestHeaders);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        if (
            $this->isGetRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/thumbnail/')
        ) {
            parse_str($queryParameters, $params);
            (new ThumbnailController($this->getCacheFolder()))($id, $params['s'] ?? 0);
            exit;
        }

        header('Content-Type: application/json');

        $config = require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php';

        $dbConfig = $config['db'];
        $fetcher = new DatabaseFetcher(new DatabaseConnection(
            $dbConfig['host'],
            $dbConfig['database'],
            $dbConfig['username'],
            $dbConfig['password'],
            DatabaseConnection::UTF8_MB4
        ));

        $apiUrl = $config['url'] ?? null;
        if (! $apiUrl) {
            http_response_code(500);
            echo json_encode(['message' => 'Project url isn\'t defined in config']);
            exit;
        }

        if ($path === '/' && $this->isGetRequest()) {
            $this->protectUsingToken($authHeader, $config);
            (new ToProcessListController(new ToProcessListQuery($fetcher)))();
            exit;
        } elseif ($path === '/text-presets' && $this->isGetRequest()) {
            $this->protectUsingToken($authHeader, $config);
            (new ListController(new ListQuery($fetcher), $this->getSerializer()))();
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/done/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new EndProcessController(new EndProcessCommand($fetcher)))($id);
            exit;
        } elseif (
            $this->isGetRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/content/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new DetailController(new ToProcessDetailQuery($fetcher), $this->getSerializer()))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/content/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new CreateController(
                new JsonBodyParser(),
                new CreateCommand($fetcher)
            ))($id, $this->getRequestBody());
            exit;
        } elseif (
            $this->isGetRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/video/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new DetailController(
                new VideoDetailQuery(
                    $fetcher,
                    new CurrentRenderStatusForVideoQuery($fetcher),
                    new SocialMediaAccountsByContentQuery($fetcher, new PredictedNextPostTimeQuery($fetcher)),
                    new PostedOnAccountsQuery($fetcher, new CurrentUploadStatusForTikTokQuery($fetcher)),
                    $this->getCacheFolder()
                ),
                $this->getSerializer()
            ))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/download-video/')
        ) {
            $this->protectUsingToken($authHeader, $config);

            $yt1dApiRepoConfig = $config['yt1dApiRepo'] ?? null;

            if (! $yt1dApiRepoConfig) {
                throw new Exception('Unset yt1dApiRepo config error');
            }

            $githubActionToken = $yt1dApiRepoConfig['token'] ?? null;

            if (! $githubActionToken) {
                throw new Exception('Unset githubActionToken config error');
            }

            $yt1dApiOwner = $yt1dApiRepoConfig['owner'] ?? null;

            if (! $yt1dApiOwner) {
                throw new Exception('Unset yt1dApiOwner config error');
            }

            $yt1dApiRepo = $yt1dApiRepoConfig['repo'] ?? null;

            if (! $yt1dApiRepo) {
                throw new Exception('Unset yt1dApiRepo config error');
            }

            (new DownloaderController(new VideoLinkQuery($fetcher), $this->getCacheFolder(), new Downloader(
                new Repository(
                    $githubActionToken,
                    $yt1dApiOwner,
                    $yt1dApiRepo
                )
            )))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/editor-state/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new UpdateController(
                new JsonBodyParser(),
                new UpdateCommand($fetcher)
            ))($id, $this->getRequestBody());
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/finish-video/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new FinishController(
                new FinishCommand($fetcher),
                $this->getSerializer()
            ))($id);
            exit;
        } elseif (
            $this->isGetRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/render/')
        ) {
            (new DisplayController(
                new CurrentRenderStatusForVideoQuery($fetcher)
            ))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/post-to-tiktok/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new PostController(
                new CanVideoBePostedOnThisTikTokAccountQuery($fetcher),
                new PostCommand($fetcher)
            ))($id, $this->getRequestBody());
            exit;
        } elseif (
            $this->isPostRequest()
            && $path === '/tiktok-video-file'
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new VideoFileController(
                new VideoFileQuery($fetcher, new CurrentRenderStatusForVideoQuery($fetcher)),
                $this->getSerializer()
            ))($this->getRequestBody());
            exit;
        } elseif (
            $this->isGetRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/subtitles/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new DetailController(
                new LanguagesAndSubtitlesQuery(
                    new YoutubeIdQuery($fetcher),
                    $config['token'] ?? '',
                    new SubtitlesApiResponseHandler(new LanguagesAndSubtitlesDenormalizer())
                ),
                $this->getSerializer()
            ))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/subtitles/')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new DetailController(
                new LanguagesAndSubtitlesUpdateQuery(
                    new YoutubeIdQuery($fetcher),
                    $config['token'] ?? '',
                    new SubtitlesApiResponseHandler(new LanguagesAndSubtitlesDenormalizer())
                ),
                $this->getSerializer()
            ))($id);
            exit;
        } elseif ($path === '/to-upload' && $this->isGetRequest()) {
            $this->protectUsingToken($authHeader, $config);
            (new ToUploadListController($apiUrl, new VideosToUploadQuery($fetcher)))();
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntBetweenPrefixAndSuffix($path, '/to-upload/', '/to-uploading')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new NonUploadedToUploadingController(
                new TikTokUploadQuery($fetcher),
                new CurrentUploadStatusForTikTokQuery($fetcher),
                new MarkAsUploadingCommand($fetcher)
            ))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntBetweenPrefixAndSuffix($path, '/uploading/', '/to-uploaded')
        ) {
            $this->protectUsingToken($authHeader, $config);
            (new UploadingToUploadedController(
                new JsonBodyParser(),
                new TikTokUploadQuery($fetcher),
                new CurrentUploadStatusForTikTokQuery($fetcher),
                new MarkAsFinishedCommand($fetcher)
            ))($id, $this->getRequestBody());
            exit;
        }

        http_response_code(404);
        exit;
    }

    protected function isGetRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function isPostRequest(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function getStringAfterPathPrefix(string $path, string $prefix): ?string
    {
        if (strpos($path, $prefix) !== 0) {
            return null;
        }

        $string = substr($path, strlen($prefix));

        return $string ?? null;
    }

    protected function getIntAfterPathPrefix(string $path, string $prefix): ?int
    {
        $id = (int) $this->getStringAfterPathPrefix($path, $prefix);

        return $id ?? null;
    }

    protected function getIntBetweenPrefixAndSuffix(string $path, string $prefix, string $suffix): ?int
    {
        $maybeIdAndSuffix = $this->getStringAfterPathPrefix($path, $prefix);

        if (! $maybeIdAndSuffix) {
            return null;
        }

        if (! str_ends_with($maybeIdAndSuffix, $suffix)) {
            return null;
        }

        $id = (int) substr($maybeIdAndSuffix, 0, strlen($maybeIdAndSuffix) - strlen($suffix));

        return $id ?? null;
    }

    protected function protectUsingToken(?string $authHeader, array $config): void
    {
        if (! isset($config['token'])) {
            throw new RuntimeException('bad config, no token');
        }

        $token = $config['token'];

        if (! $authHeader || $authHeader !== 'Bearer ' . $token) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

    protected function getRequestBody(): ?string
    {
        return file_get_contents('php://input') ?? null;
    }

    protected function getSerializer(): SerializerInterface
    {
        return new Serializer((new NormalizerFactory())->make());
    }

    protected function getCacheFolder(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    }
}
