<?php

declare(strict_types=1);

use App\Container;
use App\Commands\CleanupOldLogsCommand;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../bootstrap/app.php';
$container = new Container($config);

$command = new CleanupOldLogsCommand(
    $container->getServerRepository(),
    $container->getMonitoringLogRepository(),
    30
);
$deletedTotal = $command->execute();

echo '[' . date('Y-m-d H:i:s') . "] Deleted {$deletedTotal} old monitoring log(s)\n";
