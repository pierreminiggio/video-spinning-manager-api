<?php

namespace App;

use App\Command\EndProcessCommand;
use App\Command\Video\CreateCommand;
use App\Controller\DetailController;
use App\Controller\DownloaderController;
use App\Controller\Video\CreateController;
use App\Controller\EndProcessController;
use App\Controller\ThumbnailController;
use App\Controller\ToProcessDetailController;
use App\Controller\ToProcessListController;
use App\Http\Request\JsonBodyParser;
use App\Query\ToProcessDetailQuery;
use App\Query\ToProcessListQuery;
use App\Query\Video\VideoDetailQuery;
use App\Query\VideoLinkQuery;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;
use PierreMiniggio\MP4YoutubeVideoDownloader\Downloader;

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
        $token = $config['token'];

        if (! $authHeader || $authHeader !== 'Bearer ' . $token) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $dbConfig = $config['db'];
        $fetcher = new DatabaseFetcher(new DatabaseConnection(
            $dbConfig['host'],
            $dbConfig['database'],
            $dbConfig['username'],
            $dbConfig['password'],
            DatabaseConnection::UTF8_MB4
        ));

        if ($path === '/' && $this->isGetRequest()) {
            (new ToProcessListController(new ToProcessListQuery($fetcher)))();
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/done/')
        ) {
            (new EndProcessController(new EndProcessCommand($fetcher)))($id);
            exit;
        } elseif (
            $this->isGetRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/content/')
        ) {
            (new DetailController(new ToProcessDetailQuery($fetcher)))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/content/')
        ) {
            (new CreateController(
                new JsonBodyParser(),
                new CreateCommand($fetcher)
            ))($id, $this->getRequestBody());
            exit;
        } elseif (
            $this->isGetRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/video/')
        ) {
            (new DetailController(new VideoDetailQuery($fetcher, $this->getCacheFolder())))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/download-video/')
        ) {
            (new DownloaderController(new VideoLinkQuery($fetcher), $this->getCacheFolder(), new Downloader()))($id);
            exit;
        } elseif (
            $this->isPostRequest()
            && $id = $this->getIntAfterPathPrefix($path, '/editor-state/')
        ) {
            var_dump('editor state');
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

    protected function getIntAfterPathPrefix(string $path, string $prefix): ?int
    {
        if (strpos($path, $prefix) !== 0) {
            return null;
        }

        $id = (int) substr($path, strlen($prefix));

        return $id ?? null;
    }

    protected function getRequestBody(): ?string
    {
        return file_get_contents('php://input') ?? null;
    }

    protected function getCacheFolder(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    }
}
