<?php

use App\Query\Clear\DoneVideoQuery;
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

$doneVideoQuery = new DoneVideoQuery($fetcher);

$doneVideoIds = $doneVideoQuery->execute();
var_dump($doneVideoIds);
