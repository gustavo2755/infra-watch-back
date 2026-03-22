<?php

declare(strict_types=1);

use App\Container;
use App\Commands\RunMonitoringQueueCommand;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../bootstrap/app.php';
$container = new Container($config);
$command = new RunMonitoringQueueCommand($container->getQueueService(), 30);

$runOnce = in_array('--once', $argv, true);
$processed = $command->execute($runOnce ? 1 : null);
echo '[' . date('Y-m-d H:i:s') . "] Processed {$processed} server(s)\n";
