<?php

namespace App;

use App\Controller\ToProcessListController;
use App\Repository\ToProcessRepository;
use PierreMiniggio\DatabaseConnection\DatabaseConnection;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class App
{
    public function run(string $path, ?string $queryParameters, ?string $authHeader): void
    {

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

        if ($path === '/') {
            (new ToProcessListController(new ToProcessRepository($fetcher)))();
        }
    }
}
