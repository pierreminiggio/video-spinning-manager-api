<?php

namespace App;

use App\Command\EndProcessCommand;
use App\Controller\EndProcessController;
use App\Controller\ToProcessListController;
use App\Repository\ToProcessRepository;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

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

        $doneString = '/done/';

        if ($path === '/' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            (new ToProcessListController(new ToProcessRepository($fetcher)))();
            exit;
        } elseif (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && strpos($path, $doneString) === 0
            && $id = (int) substr($path, strlen($doneString))
        ) {
            (new EndProcessController(new EndProcessCommand($fetcher)))($id);
            exit;
        }

        http_response_code(404);
        exit;
    }
}
